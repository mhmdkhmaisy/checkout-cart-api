<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\OrderEvent;
use App\Services\WebhookService;
use App\Services\PromotionManager;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    protected $webhookService;
    protected $promotionManager;
    protected $payoutService;

    public function __construct(WebhookService $webhookService, PromotionManager $promotionManager, PayoutService $payoutService)
    {
        $this->webhookService = $webhookService;
        $this->promotionManager = $promotionManager;
        $this->payoutService = $payoutService;
    }

    public function paypal(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            Log::info('PayPal webhook received', [
                'event_type' => $payload['event_type'] ?? 'unknown',
                'resource_id' => $payload['resource']['id'] ?? 'unknown'
            ]);

            // Verify webhook (simplified - in production, verify signature)
            if (!isset($payload['event_type']) || !isset($payload['resource'])) {
                return response()->json(['success' => false, 'error' => 'Invalid webhook payload'], 400);
            }

            $eventType = $payload['event_type'];
            $resourceId = $payload['resource']['id'] ?? null;

            if (!$resourceId) {
                Log::warning('PayPal webhook missing resource ID', $payload);
                return response()->json(['success' => false, 'error' => 'Missing resource ID'], 400);
            }

            // Handle different PayPal events
            switch ($eventType) {
                case 'CHECKOUT.ORDER.APPROVED':
                    return $this->handleOrderApproved($resourceId, $payload);
                
                case 'PAYMENT.CAPTURE.COMPLETED':
                    return $this->handleCaptureCompleted($resourceId, $payload);
                
                case 'PAYMENT.CAPTURE.DENIED':
                case 'PAYMENT.CAPTURE.REFUNDED':
                case 'PAYMENT.CAPTURE.REVERSED':
                    return $this->handleCaptureFailure($resourceId, $payload, $eventType);
                
                default:
                    Log::info("PayPal webhook event not handled: {$eventType}");
                    return response()->json(['success' => true, 'message' => 'Event logged but not processed']);
            }

        } catch (\Exception $e) {
            Log::error('PayPal webhook error: ' . $e->getMessage(), [
                'payload' => $payload ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'error' => 'Webhook processing failed'], 500);
        }
    }

    private function handleOrderApproved(string $paypalOrderId, array $payload): JsonResponse
    {
        // Find local order by PayPal order ID
        $order = Order::where('payment_id', $paypalOrderId)->first();

        if (!$order) {
            Log::warning("PayPal order approved but order not found: {$paypalOrderId}");
            return response()->json(['success' => true, 'message' => 'Order not found - logged for review']);
        }

        // Log the webhook event as pending
        $this->webhookService->logWebhookEvent(
            $order->id,
            'CHECKOUT.ORDER.APPROVED',
            'pending',
            $payload
        );

        Log::info("PayPal order {$paypalOrderId} approved, attempting capture...");

        try {
            // Capture the order immediately
            $captureResult = $this->capturePayPalOrder($paypalOrderId);

            if (($captureResult['status'] ?? '') === 'COMPLETED' && $captureResult['capture_id']) {
                $order->update([
                    'paypal_capture_id' => $captureResult['capture_id'],
                    'status' => 'paid'
                ]);

                // Log the capture success
                $this->webhookService->logWebhookEvent(
                    $order->id,
                    'PAYMENT.CAPTURE.COMPLETED',
                    'paid',
                    array_merge($payload, ['capture' => $captureResult])
                );

                // Track promotion progress
                if ($order->username) {
                    $this->promotionManager->trackSpending($order->username, $order->amount);
                }

                // Process auto payouts to team members (only happens once due to idempotency check)
                try {
                    $this->payoutService->processPayoutsForOrder($order, array_merge($payload, ['capture' => $captureResult]));
                    Log::info("Auto payout processed for order {$order->id}");
                } catch (\Exception $e) {
                    Log::error("Auto payout failed for order {$order->id}: " . $e->getMessage());
                }

                Log::info("Order {$order->id} marked as paid with capture ID: {$captureResult['capture_id']}");
            } else {
                // Capture failed but order still approved
                Log::error("PayPal capture failed for order {$paypalOrderId}", ['response' => $captureResult]);
                $this->webhookService->logWebhookEvent(
                    $order->id,
                    'PAYMENT.CAPTURE.FAILED',
                    'pending',
                    array_merge($payload, ['capture' => $captureResult])
                );
            }
        } catch (\Exception $e) {
            Log::error("Exception during PayPal capture for order {$paypalOrderId}: " . $e->getMessage());
            $this->webhookService->logWebhookEvent(
                $order->id,
                'PAYMENT.CAPTURE.FAILED',
                'pending',
                array_merge($payload, ['error' => $e->getMessage()])
            );
        }

        return response()->json(['success' => true, 'message' => 'Order approval processed']);
    }


        /**
     * Capture a PayPal order by ID.
     *
     * @param string $paypalOrderId
     * @return array ['status' => 'COMPLETED|PENDING|FAILED', 'capture_id' => string|null, 'response' => array]
     * @throws \Exception
     */
    private function capturePayPalOrder(string $paypalOrderId): array
    {
        try {
            $accessToken = $this->getPayPalAccessToken();
            $apiUrl = $this->getPayPalApiUrl();

            Log::info("Attempting PayPal capture for order {$paypalOrderId}");

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
                'PayPal-Request-Id' => (string) Str::uuid(),
            ])->withBody('{}', 'application/json')
            ->post($this->getPayPalApiUrl() . "/v2/checkout/orders/{$paypalOrderId}/capture");


            $responseData = $response->json();

            if (!$response->successful()) {
                Log::error("PayPal capture failed: " . $response->body(), [
                    'order_id' => $paypalOrderId,
                    'status' => $response->status(),
                    'response' => $responseData
                ]);
                return [
                    'status' => 'FAILED',
                    'capture_id' => null,
                    'response' => $responseData
                ];
            }

            // Get first capture ID from purchase_units
            $captureId = $responseData['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;
            $captureStatus = $responseData['purchase_units'][0]['payments']['captures'][0]['status'] ?? 'PENDING';

            Log::info("PayPal capture response", [
                'order_id' => $paypalOrderId,
                'capture_id' => $captureId,
                'capture_status' => $captureStatus,
                'response' => $responseData
            ]);

            return [
                'status' => $captureStatus,
                'capture_id' => $captureId,
                'response' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error("Exception during PayPal capture for order {$paypalOrderId}: " . $e->getMessage());
            return [
                'status' => 'FAILED',
                'capture_id' => null,
                'response' => ['error' => $e->getMessage()]
            ];
        }
    }


    private function handleCaptureCompleted(string $captureId, array $payload): JsonResponse
    {
        // First try to find by capture ID, then by order ID
        $order = Order::findByPayPalId($captureId);

        // Always try to resolve by order_id if available
        $orderId = $payload['resource']['supplementary_data']['related_ids']['order_id'] ?? null;
        if (!$order && $orderId) {
            $order = Order::where('payment_id', $orderId)->first();
        }

        if ($order && !$order->paypal_capture_id) {
            // Save capture ID for future lookups
            $order->update(['paypal_capture_id' => $captureId]);
        }

        if (!$order) {
            Log::warning("PayPal capture completed but order not found: {$captureId}");
            return response()->json(['success' => true, 'message' => 'Order not found - logged for review']);
        }

        // Prevent duplicate processing
        if ($order->status === 'paid') {
            Log::info("PayPal capture {$captureId} already processed for order {$order->id}");

            // Use WebhookService to log the event properly
            $this->webhookService->logWebhookEvent(
                $order->id,
                'PAYMENT.CAPTURE.COMPLETED',
                'paid',
                $payload
            );
            return response()->json(['success' => true, 'message' => 'Order already marked as paid']);
        }

        // Verify the capture with PayPal API
        if ($this->verifyPayPalCapture($captureId)) {
            // Store the capture ID and mark as paid
            $order->update([
                'paypal_capture_id' => $captureId,
                'status' => 'paid'
            ]);

            // Use WebhookService to log the event properly
            $this->webhookService->logWebhookEvent(
                $order->id,
                'PAYMENT.CAPTURE.COMPLETED',
                'paid',
                $payload
            );

            // Track promotion progress
            if ($order->username) {
                $this->promotionManager->trackSpending($order->username, $order->amount);
            }

            Log::info("Order {$order->id} marked as paid with capture ID: {$captureId}");
            return response()->json(['success' => true, 'message' => 'Order marked as paid']);
        } else {
            // Use WebhookService to log the failed verification
            $this->webhookService->logWebhookEvent(
                $order->id,
                'PAYMENT.CAPTURE.VERIFICATION_FAILED',
                'failed',
                array_merge($payload, ['verification_error' => 'PayPal capture verification failed'])
            );

            Log::error("PayPal capture verification failed for: {$captureId}");
            return response()->json(['success' => false, 'error' => 'Capture verification failed'], 400);
        }
    }


    private function handleCaptureFailure(string $captureId, array $payload, string $eventType): JsonResponse
    {
        $order = Order::findByPayPalId($captureId);
        
        if (!$order) {
            Log::warning("PayPal capture failure but order not found: {$captureId}");
            return response()->json(['success' => true, 'message' => 'Order not found - logged for review']);
        }

        // Determine status based on event type
        $status = match($eventType) {
            'PAYMENT.CAPTURE.REFUNDED', 'PAYMENT.CAPTURE.REVERSED' => 'refunded',
            default => 'failed'
        };

        $order->update(['status' => $status]);
        
        // Use WebhookService to log the event properly
        $this->webhookService->logWebhookEvent(
            $order->id,
            $eventType,
            $status,
            $payload
        );
        
        Log::info("Order {$order->id} marked as {$status} due to {$eventType}");
        
        return response()->json(['success' => true, 'message' => "Order marked as {$status}"]);
    }

    public function coinbase(Request $request): JsonResponse
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('X-CC-Webhook-Signature');
            
            // 🔹 Log the raw body for debugging / audit
            Log::info('Coinbase raw payload', [
                'raw_body' => $payload,
                'signature' => $signature
            ]);
        
            // Verify webhook signature
            if (!$this->verifyCoinbaseSignature($payload, $signature)) {
                Log::warning('Coinbase webhook signature verification failed');
                return response()->json(['success' => false, 'error' => 'Invalid signature'], 400);
            }

            $data = json_decode($payload, true);
            Log::info('Coinbase webhook received', [
                'event_type' => $data['event']['type'] ?? 'unknown',
                'charge_id' => $data['event']['data']['id'] ?? 'unknown'
            ]);

            // Handle charge events
            $eventType = $data['event']['type'] ?? null;
            $chargeId = $data['event']['data']['id'] ?? null;
            
            if (!$chargeId) {
                Log::warning('Coinbase webhook missing charge ID', $data);
                return response()->json(['success' => false, 'error' => 'Missing charge ID'], 400);
            }

            $order = Order::where('payment_id', $chargeId)->first();
            
            if (!$order) {
                Log::warning("Coinbase charge event but order not found: {$chargeId}");
                return response()->json(['success' => true, 'message' => 'Order not found - logged for review']);
            }

            // Handle different charge events
            switch ($eventType) {
                case 'charge:confirmed':
                    if ($order->status !== 'paid') {
                        $order->update(['status' => 'paid']);
                        $this->webhookService->logWebhookEvent(
                            $order->id,
                            'charge:confirmed',
                            'paid',
                            $data
                        );
                        
                        // Track promotion progress
                        if ($order->username) {
                            $this->promotionManager->trackSpending($order->username, $order->amount);
                        }
                        
                        Log::info("Order {$order->id} marked as paid via Coinbase");
                    }
                    break;
                
                case 'charge:failed':
                case 'charge:canceled':
                    $order->update(['status' => 'failed']);
                    $this->webhookService->logWebhookEvent(
                        $order->id,
                        $eventType,
                        'failed',
                        $data
                    );
                    Log::info("Order {$order->id} marked as failed via Coinbase: {$eventType}");
                    break;
                
                case 'charge:resolved':
                    $order->update(['status' => 'refunded']);
                    $this->webhookService->logWebhookEvent(
                        $order->id,
                        'charge:resolved',
                        'refunded',
                        $data
                    );
                    Log::info("Order {$order->id} marked as refunded via Coinbase");
                    break;
                
                case 'charge:pending':
                    // Keep as pending, just log
                    $this->webhookService->logWebhookEvent(
                        $order->id,
                        'charge:pending',
                        $order->status, // Keep current status
                        $data
                    );
                    Log::info("Order {$order->id} charge pending via Coinbase");
                    break;
                
                default:
                    Log::info("Coinbase webhook event not handled: {$eventType}");
                    // Still log unknown events
                    $this->webhookService->logWebhookEvent(
                        $order->id,
                        $eventType,
                        $order->status,
                        $data
                    );
            }

            return response()->json(['success' => true, 'message' => 'Webhook processed successfully']);
        } catch (\Exception $e) {
            Log::error('Coinbase webhook error: ' . $e->getMessage(), [
                'payload' => $payload ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'error' => 'Webhook processing failed'], 500);
        }
    }

    private function verifyPayPalCapture(string $captureId): bool
    {
        try {
            $accessToken = $this->getPayPalAccessToken();
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json'
            ])->get($this->getPayPalApiUrl() . "/v2/payments/captures/{$captureId}");

            if ($response->successful()) {
                $captureData = $response->json();
                return $captureData['status'] === 'COMPLETED';
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('PayPal capture verification failed: ' . $e->getMessage());
            return false;
        }
    }

    private function verifyCoinbaseSignature(string $payload, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }

        $webhookSecret = config('services.coinbase.webhook_secret');
        if (!$webhookSecret) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }

    private function getPayPalAccessToken(): string
    {
        $response = Http::asForm()
            ->withBasicAuth(
                config('services.paypal.client_id'),
                config('services.paypal.client_secret')
            )
            ->post($this->getPayPalApiUrl() . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);

        if (!$response->successful()) {
            throw new \Exception('PayPal authentication failed');
        }

        return $response->json()['access_token'];
    }

    private function getPayPalApiUrl(): string
    {
        return config('services.paypal.base_url', 'https://api-m.sandbox.paypal.com');
    }
}