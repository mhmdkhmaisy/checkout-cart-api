<?php

namespace App\Services;

use App\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DiscordWebhookService
{
    protected $timeout = 10;
    protected $retries = 2;

    public function getActiveWebhooks(string $eventType)
    {
        return Cache::remember("webhooks.{$eventType}", 300, function() use ($eventType) {
            return Webhook::active()->forEvent($eventType)->get();
        });
    }

    public function sendNotification(string $eventType, array $payload)
    {
        $webhooks = $this->getActiveWebhooks($eventType);

        if ($webhooks->isEmpty()) {
            return;
        }

        foreach ($webhooks as $webhook) {
            $this->sendToWebhook($webhook, $payload);
        }
    }

    protected function sendToWebhook(Webhook $webhook, array $payload)
    {
        $attempt = 0;
        $success = false;

        while ($attempt < $this->retries && !$success) {
            $attempt++;

            try {
                $response = Http::timeout($this->timeout)
                    ->post($webhook->url, $payload);

                if ($response->successful()) {
                    $success = true;
                    Log::info("Discord webhook sent successfully", [
                        'webhook_id' => $webhook->id,
                        'webhook_name' => $webhook->name,
                        'event' => $webhook->event_type,
                    ]);
                } else {
                    Log::warning("Discord webhook failed", [
                        'webhook_id' => $webhook->id,
                        'status' => $response->status(),
                        'attempt' => $attempt,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Discord webhook exception", [
                    'webhook_id' => $webhook->id,
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                ]);
            }

            if (!$success && $attempt < $this->retries) {
                usleep(500000);
            }
        }

        return $success;
    }

    public function buildPromotionCreatedPayload($promotion)
    {
        $message = "🎁 **New Promotion Created!**\n\n";
        $message .= "**{$promotion->title}**\n";
        $message .= "{$promotion->description}\n\n";
        $message .= "**Requirements:**\n";
        $message .= "• Spend: $" . number_format($promotion->min_amount, 2) . "\n";
        $message .= "• Type: " . ucfirst($promotion->bonus_type) . "\n";
        
        if ($promotion->claim_limit_per_user) {
            $message .= "• Per User Limit: {$promotion->claim_limit_per_user} claim(s)\n";
        }
        
        if ($promotion->global_claim_limit) {
            $message .= "• Global Limit: {$promotion->global_claim_limit} total claims\n";
        }
        
        $message .= "\n**Duration:**\n";
        $message .= "• Start: <t:" . $promotion->start_at->timestamp . ":F>\n";
        
        if ($promotion->end_at) {
            $message .= "• End: <t:" . $promotion->end_at->timestamp . ":F>\n";
        } else {
            $message .= "• End: No expiration\n";
        }

        $itemsList = '';
        if (!empty($promotion->reward_items)) {
            foreach ($promotion->reward_items as $item) {
                $itemsList .= "• " . $item['item_amount'] . "x " . $item['item_name'] . "\n";
            }
        }

        $fields = [
            [
                'name' => 'Required Amount',
                'value' => '$' . number_format($promotion->min_amount, 2),
                'inline' => true
            ],
            [
                'name' => 'Type',
                'value' => ucfirst($promotion->bonus_type),
                'inline' => true
            ],
            [
                'name' => 'Global Limit',
                'value' => $promotion->global_claim_limit ?: 'Unlimited',
                'inline' => true
            ],
        ];

        if ($itemsList) {
            $fields[] = [
                'name' => '🎁 Reward Items',
                'value' => $itemsList,
                'inline' => false
            ];
        }

        return [
            'content' => $message,
            'embeds' => [
                [
                    'title' => '🎁 ' . $promotion->title,
                    'description' => $promotion->description,
                    'color' => 5814783,
                    'fields' => $fields,
                    'footer' => [
                        'text' => 'Promotion ID: ' . $promotion->id
                    ],
                    'timestamp' => now()->toIso8601String()
                ]
            ]
        ];
    }

    public function buildPromotionClaimedPayload($promotion, $claim, $username)
    {
        $claimsRemaining = null;
        if ($promotion->global_claim_limit) {
            $claimsRemaining = $promotion->global_claim_limit - $promotion->claimed_global;
        }

        $message = "✅ **Promotion Claimed!**\n\n";
        $message .= "**User:** {$username}\n";
        $message .= "**Promotion:** {$promotion->title}\n\n";
        $message .= "**Stats:**\n";
        $message .= "• Total Claims: {$promotion->claimed_global}";
        
        if ($claimsRemaining !== null) {
            $message .= " / {$promotion->global_claim_limit}\n";
            $message .= "• Remaining: {$claimsRemaining}\n";
        } else {
            $message .= "\n";
        }
        
        if ($promotion->end_at) {
            $daysLeft = now()->diffInDays($promotion->end_at, false);
            if ($daysLeft >= 0) {
                $message .= "• Ends in: {$daysLeft} day(s)\n";
            } else {
                $message .= "• Status: Expired\n";
            }
        }

        $itemsList = '';
        if (!empty($promotion->reward_items)) {
            foreach ($promotion->reward_items as $item) {
                $itemsList .= "• " . $item['item_amount'] . "x " . $item['item_name'] . "\n";
            }
        }

        $fields = [
            [
                'name' => 'Total Claims',
                'value' => (string)$promotion->claimed_global,
                'inline' => true
            ],
            [
                'name' => 'Claims Remaining',
                'value' => $claimsRemaining !== null ? (string)$claimsRemaining : 'Unlimited',
                'inline' => true
            ],
        ];

        if ($itemsList) {
            $fields[] = [
                'name' => '🎁 Claimed Items',
                'value' => $itemsList,
                'inline' => false
            ];
        }

        return [
            'content' => $message,
            'embeds' => [
                [
                    'title' => '✅ Promotion Claimed',
                    'description' => "**{$username}** claimed **{$promotion->title}**",
                    'color' => 3066993,
                    'fields' => $fields,
                    'footer' => [
                        'text' => 'Promotion ID: ' . $promotion->id
                    ],
                    'timestamp' => now()->toIso8601String()
                ]
            ]
        ];
    }

    public function clearCache()
    {
        Cache::forget('webhooks.promotion.created');
        Cache::forget('webhooks.promotion.claimed');
        Cache::forget('webhooks.update.published');
    }
}
