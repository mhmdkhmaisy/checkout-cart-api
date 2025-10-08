<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderLog;
use App\Models\OrderEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WebhookService
{
    /**
     * Verify PayPal webhook signature
     */
    public function verifyPayPalWebhook($headers, $payload, $webhookId)
    {
        $requiredHeaders = [
            'PAYPAL-AUTH-ALGO',
            'PAYPAL-CERT-URL', 
            'PAYPAL-TRANSMISSION-ID',
            'PAYPAL-TRANSMISSION-SIG',
            'PAYPAL-TRANSMISSION-TIME'
        ];

        // Log headers for debugging
        Log::info('PayPal webhook headers received', ['headers' => $headers]);

        foreach ($requiredHeaders as $header) {
            if (!isset($headers[$header])) {
                Log::warning("Missing PayPal webhook header: {$header}");
                return false;
            }
        }

        $verificationData = [
            'auth_algo' => $headers['PAYPAL-AUTH-ALGO'],
            'cert_url' => $headers['PAYPAL-CERT-URL'],
            'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'],
            'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'],
            'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'],
            'webhook_id' => $webhookId,
            'webhook_event' => json_decode($payload, true)
        ];

        Log::info('PayPal verification data', ['data' => $verificationData]);

        try {
            $accessToken = $this->getPayPalAccessToken();
            if (!$accessToken) {
                Log::error('Failed to get PayPal access token');
                return false;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post(config('services.paypal.base_url') . '/v1/notifications/verify-webhook-signature', $verificationData);

            Log::info('PayPal verification response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('PayPal verification request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

            $result = $response->json();
            $isValid = isset($result['verification_status']) && $result['verification_status'] === 'SUCCESS';
            
            Log::info('PayPal verification result', [
                'verification_status' => $result['verification_status'] ?? 'UNKNOWN',
                'is_valid' => $isValid
            ]);

            return $isValid;
        } catch (\Exception $e) {
            Log::error('PayPal webhook verification failed: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Verify Coinbase webhook signature
     */
    public function verifyCoinbaseWebhook($signature, $payload, $secret)
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($signature, $expectedSignature);
    }

    /**
     * Get PayPal access token
     */
    private function getPayPalAccessToken()
    {
        try {
            $response = Http::withBasicAuth(
                config('services.paypal.client_id'),
                config('services.paypal.client_secret')
            )->asForm()->post(config('services.paypal.base_url') . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);

            if (!$response->successful()) {
                Log::error('Failed to get PayPal access token', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $result = $response->json();
            return $result['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('PayPal access token request failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log webhook event and update order status
     */
    public function logWebhookEvent($orderId, $eventType, $newStatus, $payload, $userId = null)
    {
        $order = Order::find($orderId);
        if (!$order) {
            Log::warning("Order not found for webhook event", [
                'order_id' => $orderId,
                'event_type' => $eventType,
                'status' => $newStatus
            ]);
            return false;
        }

        $oldStatus = $order->status;
        $shouldUpdateStatus = false;

        // Determine if we should update the order status
        if ($eventType === 'CHECKOUT.ORDER.APPROVED') {
            // For order approval, only update if order is still pending
            if ($order->status === 'pending') {
                $shouldUpdateStatus = true;
            }
            // Always log the event, but don't change status if already processed
            Log::info("PayPal order approved event logged", [
                'order_id' => $orderId,
                'current_status' => $order->status,
                'event_status' => $newStatus,
                'will_update_status' => $shouldUpdateStatus
            ]);
        } elseif (in_array($eventType, ['PAYMENT.CAPTURE.COMPLETED', 'charge:confirmed'])) {
            // For payment completion, only update if not already paid
            if ($order->status !== 'paid') {
                $shouldUpdateStatus = true;
            }
            Log::info("Payment completion event logged", [
                'order_id' => $orderId,
                'current_status' => $order->status,
                'event_status' => $newStatus,
                'will_update_status' => $shouldUpdateStatus
            ]);
        } elseif (in_array($eventType, ['PAYMENT.CAPTURE.DENIED', 'PAYMENT.CAPTURE.REFUNDED', 'PAYMENT.CAPTURE.REVERSED', 'charge:failed', 'charge:canceled', 'charge:resolved'])) {
            // For failures/refunds, always update unless already in that state
            if ($order->status !== $newStatus) {
                $shouldUpdateStatus = true;
            }
        } else {
            // For other events, update if different status
            if ($order->status !== $newStatus) {
                $shouldUpdateStatus = true;
            }
        }

        // Update order status if needed
        if ($shouldUpdateStatus) {
            $order->update(['status' => $newStatus]);
            
            Log::info("Order status updated via webhook", [
                'order_id' => $orderId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'event_type' => $eventType
            ]);
        } else {
            Log::info("Order status not updated - already in appropriate state", [
                'order_id' => $orderId,
                'current_status' => $order->status,
                'event_status' => $newStatus,
                'event_type' => $eventType
            ]);
        }

        // ALWAYS log the event to OrderEvent table (full history)
        OrderEvent::create([
            'order_id' => $orderId,
            'event_type' => $eventType,
            'status' => $newStatus,
            'payload' => $payload
        ]);

        // Update OrderLog with the latest event (current state)
        OrderLog::updateOrCreate(
            ['order_id' => $orderId],
            [
                'username' => $order->username,
                'status' => $order->status, // Use current order status, not event status
                'last_event' => $eventType,
                'payload' => $payload
            ]
        );

        Log::info("Webhook event logged successfully", [
            'order_id' => $orderId,
            'event_type' => $eventType,
            'final_order_status' => $order->status
        ]);

        return true;
    }

    /**
     * Map PayPal event to status
     */
    public function mapPayPalEventToStatus($eventType)
    {
        $mapping = [
            'CHECKOUT.ORDER.APPROVED' => 'pending',
            'PAYMENT.CAPTURE.COMPLETED' => 'paid',
            'PAYMENT.CAPTURE.DENIED' => 'failed',
            'PAYMENT.CAPTURE.REFUNDED' => 'refunded',
            'PAYMENT.CAPTURE.REVERSED' => 'refunded',
            'PAYMENT.CAPTURE.PENDING' => 'pending',
        ];

        return $mapping[$eventType] ?? null;
    }

    /**
     * Map Coinbase event to status
     */
    public function mapCoinbaseEventToStatus($eventType)
    {
        $mapping = [
            'charge:pending' => 'pending',
            'charge:confirmed' => 'paid',
            'charge:failed' => 'failed',
            'charge:canceled' => 'failed',
            'charge:resolved' => 'refunded',
        ];

        return $mapping[$eventType] ?? null;
    }

    /**
     * Should we update order status for this event?
     */
    public function shouldUpdateOrderStatus($eventType, $provider)
    {
        if ($provider === 'paypal') {
            // Only update status for capture events, not approval
            return in_array($eventType, [
                'PAYMENT.CAPTURE.COMPLETED',
                'PAYMENT.CAPTURE.DENIED', 
                'PAYMENT.CAPTURE.REFUNDED',
                'PAYMENT.CAPTURE.REVERSED',
                'PAYMENT.CAPTURE.PENDING'
            ]);
        }

        if ($provider === 'coinbase') {
            // Update for all known coinbase events
            return in_array($eventType, [
                'charge:pending',
                'charge:confirmed',
                'charge:failed',
                'charge:canceled',
                'charge:resolved'
            ]);
        }

        return false;
    }
}