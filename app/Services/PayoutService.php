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
        Log::info("DEBUG: Starting payout processing for order {$order->id}");
        
        if (Payout::hasPayoutForOrder($order->id)) {
            Log::info("Payouts already exist for order {$order->id}, skipping");
            return true;
        }

        $netAmount = $this->extractNetAmount($captureData);
        $grossAmount = $this->extractGrossAmount($captureData);
        $currency = $captureData['resource']['amount']['currency_code'] ?? $order->currency ?? 'USD';

        Log::info("DEBUG: Extracted payout data", [
            'order_id' => $order->id,
            'net_amount' => $netAmount,
            'gross_amount' => $grossAmount,
            'currency' => $currency,
        ]);

        if ($netAmount <= 0) {
            Log::warning("Net amount is zero or negative for order {$order->id}", [
                'net_amount' => $netAmount,
                'capture_data_keys' => array_keys($captureData),
                'resource_keys' => isset($captureData['resource']) ? array_keys($captureData['resource']) : [],
            ]);
            return false;
        }

        $teamMembers = TeamMember::active()->get();
        
        Log::info("DEBUG: Found team members", [
            'count' => $teamMembers->count(),
            'members' => $teamMembers->pluck('name', 'id')->toArray(),
        ]);
        
        if ($teamMembers->isEmpty()) {
            Log::info("No active team members for payout on order {$order->id}");
            return true;
        }

        DB::beginTransaction();
        try {
            $payoutRecords = [];
            
            foreach ($teamMembers as $member) {
                $payoutAmount = round(($netAmount * $member->percentage) / 100, 2);
                
                Log::info("DEBUG: Calculating payout for member", [
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'percentage' => $member->percentage,
                    'net_amount' => $netAmount,
                    'payout_amount' => $payoutAmount,
                ]);
                
                if ($payoutAmount < 0.01) {
                    Log::info("DEBUG: Skipping member - payout too small", [
                        'member_id' => $member->id,
                        'payout_amount' => $payoutAmount,
                    ]);
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

                Log::info("DEBUG: Created payout record", [
                    'payout_id' => $payout->id,
                    'member_id' => $member->id,
                    'amount' => $payoutAmount,
                ]);

                $payoutRecords[] = $payout;
            }

            Log::info("DEBUG: Total payout records created", ['count' => count($payoutRecords)]);

            if (!empty($payoutRecords)) {
                Log::info("DEBUG: Executing PayPal payouts");
                $this->executePayPalPayouts($payoutRecords, $currency);
            } else {
                Log::warning("DEBUG: No payout records to send to PayPal");
            }

            DB::commit();
            Log::info("DEBUG: Payout processing completed successfully for order {$order->id}");
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payout processing failed for order {$order->id}: " . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    protected function extractNetAmount(array $captureData): float
    {
        $resource = $captureData['resource'] ?? [];
        
        Log::info("DEBUG: Extracting net amount", [
            'has_resource' => !empty($resource),
            'has_seller_receivable_breakdown' => isset($resource['seller_receivable_breakdown']),
            'seller_receivable_keys' => isset($resource['seller_receivable_breakdown']) ? array_keys($resource['seller_receivable_breakdown']) : [],
        ]);
        
        if (isset($resource['seller_receivable_breakdown']['net_amount']['value'])) {
            $netAmount = (float) $resource['seller_receivable_breakdown']['net_amount']['value'];
            Log::info("DEBUG: Found net_amount in seller_receivable_breakdown", ['net_amount' => $netAmount]);
            return $netAmount;
        }

        if (isset($resource['amount']['value'])) {
            $gross = (float) $resource['amount']['value'];
            if (isset($resource['seller_receivable_breakdown']['paypal_fee']['value'])) {
                $fee = (float) $resource['seller_receivable_breakdown']['paypal_fee']['value'];
                $netAmount = $gross - $fee;
                Log::info("DEBUG: Calculated net_amount from gross - fee", [
                    'gross' => $gross,
                    'fee' => $fee,
                    'net_amount' => $netAmount,
                ]);
                return $netAmount;
            }
            Log::info("DEBUG: Using gross amount (no fee found)", ['gross' => $gross]);
            return $gross;
        }

        Log::warning("DEBUG: Could not extract net amount from capture data", [
            'capture_data_keys' => array_keys($captureData),
            'resource_keys' => array_keys($resource),
        ]);
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
                $note = "You have received {$payout->percentage}% of \${$payout->net_amount} which amounts to \${$payout->payout_amount} from Order #{$payout->order_id}";
                $items[] = [
                    'recipient_type' => 'EMAIL',
                    'amount' => [
                        'value' => number_format($payout->payout_amount, 2, '.', ''),
                        'currency' => $currency,
                    ],
                    'receiver' => $payout->paypal_email,
                    'note' => $note,
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
