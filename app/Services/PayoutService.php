<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payout;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayoutService
{
    public function processPayoutsForOrder(Order $order, array $captureData): bool
    {
        if (Payout::hasPayoutForOrder($order->id)) {
            Log::info("Payouts already exist for order {$order->id}, skipping");
            return true;
        }

        $netAmount = $this->extractNetAmount($captureData);
        $grossAmount = $this->extractGrossAmount($captureData);
        $currency = $captureData['resource']['amount']['currency_code'] ?? $order->currency ?? 'USD';

        if ($netAmount <= 0) {
            Log::warning("Net amount is zero or negative for order {$order->id}");
            return false;
        }

        $teamMembers = TeamMember::active()->get();
        
        if ($teamMembers->isEmpty()) {
            Log::info("No active team members for payout on order {$order->id}");
            return true;
        }

        DB::beginTransaction();
        try {
            $payoutRecords = [];
            
            foreach ($teamMembers as $member) {
                $payoutAmount = round(($netAmount * $member->percentage) / 100, 2);
                
                if ($payoutAmount < 0.01) {
                    continue;
                }

                $payout = Payout::create([
                    'order_id' => $order->id,
                    'team_member_id' => $member->id,
                    'paypal_email' => $member->paypal_email,
                    'gross_amount' => $grossAmount,
                    'net_amount' => $netAmount,
                    'payout_amount' => $payoutAmount,
                    'percentage' => $member->percentage,
                    'currency' => $currency,
                    'status' => 'pending',
                ]);

                $payoutRecords[] = $payout;
            }

            if (!empty($payoutRecords)) {
                $this->executePayPalPayouts($payoutRecords, $currency);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payout processing failed for order {$order->id}: " . $e->getMessage());
            return false;
        }
    }

    protected function extractNetAmount(array $captureData): float
    {
        $resource = $captureData['resource'] ?? [];
        
        if (isset($resource['seller_receivable_breakdown']['net_amount']['value'])) {
            return (float) $resource['seller_receivable_breakdown']['net_amount']['value'];
        }

        if (isset($resource['amount']['value'])) {
            $gross = (float) $resource['amount']['value'];
            if (isset($resource['seller_receivable_breakdown']['paypal_fee']['value'])) {
                $fee = (float) $resource['seller_receivable_breakdown']['paypal_fee']['value'];
                return $gross - $fee;
            }
            return $gross;
        }

        return 0;
    }

    protected function extractGrossAmount(array $captureData): float
    {
        $resource = $captureData['resource'] ?? [];
        
        if (isset($resource['seller_receivable_breakdown']['gross_amount']['value'])) {
            return (float) $resource['seller_receivable_breakdown']['gross_amount']['value'];
        }

        if (isset($resource['amount']['value'])) {
            return (float) $resource['amount']['value'];
        }

        return 0;
    }

    protected function executePayPalPayouts(array $payouts, string $currency): void
    {
        if (empty($payouts)) {
            return;
        }

        try {
            $accessToken = $this->getPayPalAccessToken();
            $batchId = 'batch_' . Str::uuid();

            $items = [];
            foreach ($payouts as $payout) {
                $items[] = [
                    'recipient_type' => 'EMAIL',
                    'amount' => [
                        'value' => number_format($payout->payout_amount, 2, '.', ''),
                        'currency' => $currency,
                    ],
                    'receiver' => $payout->paypal_email,
                    'note' => "Revenue share payout for Order #{$payout->order_id}",
                    'sender_item_id' => "payout_{$payout->id}",
                ];
            }

            $payoutRequest = [
                'sender_batch_header' => [
                    'sender_batch_id' => $batchId,
                    'email_subject' => 'You have received a payment!',
                    'email_message' => 'You have received a revenue share payout.',
                ],
                'items' => $items,
            ];

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ])->post($this->getPayPalApiUrl() . '/v1/payments/payouts', $payoutRequest);

            $responseData = $response->json();

            if ($response->successful()) {
                $paypalBatchId = $responseData['batch_header']['payout_batch_id'] ?? null;
                
                foreach ($payouts as $payout) {
                    $payout->update([
                        'paypal_batch_id' => $paypalBatchId,
                        'status' => 'processing',
                    ]);
                }

                Log::info("PayPal payout batch created: {$paypalBatchId}", [
                    'batch_id' => $batchId,
                    'payout_count' => count($payouts),
                ]);
            } else {
                Log::error("PayPal payout failed", [
                    'response' => $responseData,
                    'status' => $response->status(),
                ]);

                foreach ($payouts as $payout) {
                    $payout->update([
                        'status' => 'failed',
                        'error_message' => json_encode($responseData),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("PayPal payout exception: " . $e->getMessage());

            foreach ($payouts as $payout) {
                $payout->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function getPayPalAccessToken(): string
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

    protected function getPayPalApiUrl(): string
    {
        return config('services.paypal.base_url', 'https://api-m.sandbox.paypal.com');
    }

    public function getPayoutStats(): array
    {
        return [
            'total_payouts' => Payout::count(),
            'completed_payouts' => Payout::completed()->count(),
            'pending_payouts' => Payout::pending()->count(),
            'failed_payouts' => Payout::failed()->count(),
            'total_paid_out' => Payout::completed()->sum('payout_amount'),
        ];
    }
}
