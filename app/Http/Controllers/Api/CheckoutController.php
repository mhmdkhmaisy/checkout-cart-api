<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function checkout(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|string',
                'payment_method' => 'required|in:paypal,coinbase',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer',
                'items.*.name' => 'required|string',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.quantity' => 'required|integer|min:1',
                'currency' => 'nullable|string|in:USD,EUR,GBP'
            ]);

            $currency = $validated['currency'] ?? 'USD';
            $items = $validated['items'];
            $totalAmount = collect($items)->sum(fn($item) => $item['price'] * $item['quantity']);

            // Create order and order items in a transaction
            $order = DB::transaction(function () use ($validated, $items, $totalAmount, $currency) {
                // Create the order
                $order = new Order();
                $order->username = $validated['user_id'];
                $order->payment_method = $validated['payment_method'];
                $order->amount = $totalAmount;
                $order->currency = $currency;
                $order->status = 'pending';
                $order->payment_id = null;
                $order->save();

                Log::info("Order created", [
                    'order_id' => $order->id,
                    'user_id' => $validated['user_id'],
                    'amount' => $totalAmount
                ]);

                // Create order items using the relationship
                foreach ($items as $item) {
                    $product = Product::find($item['product_id']);
                    
                    if (!$product) {
                        Log::warning("Product not found during checkout", [
                            'product_id' => $item['product_id'],
                            'item_name' => $item['name']
                        ]);
                    }

                    $order->items()->create([
                        'product_id' => $product ? $product->id : null,
                        'product_name' => $product ? $product->product_name : $item['name'],
                        'price' => $item['price'],
                        'qty_units' => $item['quantity'],
                        'total_qty' => $item['quantity'] * ($product ? $product->qty_unit : 1),
                        'claimed' => false
                    ]);
                }

                Log::info("Order items created", [
                    'order_id' => $order->id,
                    'items_count' => count($items)
                ]);

                return $order;
            });

            // Process payment based on method
            if ($validated['payment_method'] === 'paypal') {
                $paymentResponse = $this->createPayPalOrder($order, $items, $totalAmount, $currency);
            } else {
                $paymentResponse = $this->createCoinbaseCharge($order, $items, $totalAmount, $currency);
            }

            if (!$paymentResponse['success']) {
                $order->update(['status' => 'failed']);
                return response()->json([
                    'success' => false,
                    'error' => $paymentResponse['error']
                ], 400);
            }

            // Update order with payment ID
            $order->update(['payment_id' => $paymentResponse['payment_id']]);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'payment_url' => $paymentResponse['payment_url'],
                'payment_id' => $paymentResponse['payment_id'],
                'message' => 'Checkout initiated successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Checkout error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Checkout processing failed'
            ], 500);
        }
    }

    private function createPayPalOrder($order, $items, $amount, $currency): array
    {
        try {
            $accessToken = $this->getPayPalAccessToken();
            
            $paypalItems = collect($items)->map(function($item) use ($currency) {
                return [
                    'name' => $item['name'],
                    'quantity' => (string)$item['quantity'],
                    'unit_amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($item['price'], 2, '.', '')
                    ]
                ];
            })->toArray();

            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => "order_{$order->id}",
                        'amount' => [
                            'currency_code' => $currency,
                            'value' => number_format($amount, 2, '.', ''),
                            'breakdown' => [
                                'item_total' => [
                                    'currency_code' => $currency,
                                    'value' => number_format($amount, 2, '.', '')
                                ]
                            ]
                        ],
                        'items' => $paypalItems
                    ]
                ],
                'application_context' => [
                    'return_url' => route('payment.success', ['order_id' => $order->id]),
                    'cancel_url' => route('payment.cancel', ['order_id' => $order->id]),
                    'brand_name' => config('app.name', 'RSPS Store'),
                    'landing_page' => 'BILLING',
                    'user_action' => 'PAY_NOW'
                ]
            ];

            Log::info('Creating PayPal order', [
                'order_id' => $order->id,
                'amount' => $amount,
                'currency' => $currency
            ]);

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
                'PayPal-Request-Id' => (string) Str::uuid(),
            ])->post($this->getPayPalApiUrl() . '/v2/checkout/orders', $orderData);

            if (!$response->successful()) {
                Log::error('PayPal order creation failed', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return [
                    'success' => false,
                    'error' => 'PayPal order creation failed'
                ];
            }

            $responseData = $response->json();
            $approvalUrl = collect($responseData['links'])->firstWhere('rel', 'approve')['href'] ?? null;

            Log::info('PayPal order created successfully', [
                'order_id' => $order->id,
                'paypal_order_id' => $responseData['id']
            ]);

            if (!$approvalUrl) {
                return [
                    'success' => false,
                    'error' => 'PayPal approval URL not found'
                ];
            }

            return [
                'success' => true,
                'payment_id' => $responseData['id'],
                'payment_url' => $approvalUrl
            ];

        } catch (\Exception $e) {
            Log::error('PayPal order creation error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => 'PayPal integration error'
            ];
        }
    }

    private function createCoinbaseCharge($order, $items, $amount, $currency): array
    {
        try {
            $chargeData = [
                'name' => "Order #{$order->id}",
                'description' => "RSPS Store Purchase - " . count($items) . " items",
                'pricing_type' => 'fixed_price',
                'local_price' => [
                    'amount' => number_format($amount, 2, '.', ''),
                    'currency' => $currency
                ],
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => $order->username
                ],
                'redirect_url' => route('payment.success', ['order_id' => $order->id]),
                'cancel_url' => route('payment.cancel', ['order_id' => $order->id])
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.coinbase.api_key'),
                'Content-Type' => 'application/json',
                'X-CC-Api-Key' => config('services.coinbase.api_key')
            ])->post('https://api.commerce.coinbase.com/charges', $chargeData);

            if (!$response->successful()) {
                Log::error('Coinbase charge creation failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return [
                    'success' => false,
                    'error' => 'Coinbase charge creation failed'
                ];
            }

            $responseData = $response->json();
            
            return [
                'success' => true,
                'payment_id' => $responseData['data']['id'],
                'payment_url' => $responseData['data']['hosted_url']
            ];

        } catch (\Exception $e) {
            Log::error('Coinbase charge creation error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Coinbase integration error'
            ];
        }
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
            Log::error('PayPal authentication failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            throw new \Exception('PayPal authentication failed');
        }

        return $response->json()['access_token'];
    }

    private function getPayPalApiUrl(): string
    {
        return config('services.paypal.base_url', 'https://api-m.sandbox.paypal.com');
    }

    public function paypalSuccess(Request $request): JsonResponse
    {
        Log::info('PayPal success callback received', [
            'query_params' => $request->query()
        ]);

        $paypalOrderId = $request->query('token');
        if ($paypalOrderId) {
            $this->capturePayPalOrder($paypalOrderId);
        }

        return response()->json([
            'success' => true,
            'message' => 'PayPal payment completed successfully',
            'data' => $request->all()
        ]);
    }

    public function paypalCancel(Request $request): JsonResponse
    {
        Log::info('PayPal cancel callback received', [
            'query_params' => $request->query()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'PayPal payment was cancelled',
            'data' => $request->all()
        ]);
    }

    private function capturePayPalOrder(string $paypalOrderId): void
    {
        try {
            Log::info('Attempting to capture PayPal order', [
                'paypal_order_id' => $paypalOrderId
            ]);

            $accessToken = $this->getPayPalAccessToken();
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
                'PayPal-Request-Id' => (string) Str::uuid(),
            ])->withBody('{}', 'application/json')
            ->post($this->getPayPalApiUrl() . "/v2/checkout/orders/{$paypalOrderId}/capture");

            $captureData = $response->json();

            if (isset($captureData['name']) && $captureData['name'] === 'ORDER_ALREADY_CAPTURED') {
                $captureId = $captureData['details'][0]['issue'] ?? null;
                if (!$captureId && isset($captureData['purchase_units'][0]['payments']['captures'][0]['id'])) {
                    $captureId = $captureData['purchase_units'][0]['payments']['captures'][0]['id'];
                }

                Log::warning('PayPal order already captured', [
                    'paypal_order_id' => $paypalOrderId,
                    'capture_id' => $captureId
                ]);

                $order = Order::where('payment_id', $paypalOrderId)->first();
                if ($order) {
                    $order->update([
                        'status' => 'paid',
                        'paypal_capture_id' => $captureId
                    ]);
                }
                return;
            }

            if ($response->successful()) {
                $captureId = $captureData['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;

                Log::info('PayPal order captured successfully', [
                    'paypal_order_id' => $paypalOrderId,
                    'capture_id' => $captureId
                ]);

                $order = Order::where('payment_id', $paypalOrderId)->first();
                if ($order) {
                    $order->update([
                        'status' => 'paid',
                        'paypal_capture_id' => $captureId
                    ]);
                }
            } else {
                Log::error('PayPal capture failed', [
                    'paypal_order_id' => $paypalOrderId,
                    'error_response' => $captureData
                ]);
            }

        } catch (\Exception $e) {
            Log::error('PayPal capture error', [
                'paypal_order_id' => $paypalOrderId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function coinbaseSuccess(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Coinbase payment completed successfully',
            'data' => $request->all()
        ]);
    }

    public function coinbaseCancel(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Coinbase payment was cancelled',
            'data' => $request->all()
        ]);
    }
}
