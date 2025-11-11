<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\PromotionClaim;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromotionManager
{
    protected $discordWebhook;

    public function __construct(DiscordWebhookService $discordWebhook)
    {
        $this->discordWebhook = $discordWebhook;
    }
    /**
     * Get all active promotions (cached for performance)
     */
    public function getActivePromotions()
    {
        return Cache::remember('active_promotions', 60, function() {
            return Promotion::active()->get();
        });
    }

    /**
     * Get all promotions for admin panel
     */
    public function getAllPromotions()
    {
        return Promotion::orderBy('created_at', 'desc')->get();
    }

    /**
     * Evaluate user progress across all active promotions
     */
    public function evaluateUserProgress(string $username)
    {
        $promos = $this->getActivePromotions();
        
        return $promos->map(function($promo) use ($username) {
            return $this->calculateProgress($promo, $username);
        });
    }

    /**
     * Calculate progress for a specific promotion and user
     */
    protected function calculateProgress(Promotion $promo, string $username)
    {
        $claim = PromotionClaim::where('promotion_id', $promo->id)
            ->where('username', $username)
            ->first();

        if (!$claim) {
            return [
                'promo' => $promo,
                'progress' => 0,
                'progress_percent' => 0,
                'progress_amount' => 0,
                'can_claim' => false,
                'claim_count' => 0,
            ];
        }

        $progress = $promo->min_amount > 0 
            ? min(1, $claim->total_spent_during_promo / $promo->min_amount)
            : 0;

        return [
            'promo' => $promo,
            'progress' => $progress,
            'progress_percent' => $progress * 100,
            'progress_amount' => $claim->total_spent_during_promo,
            'can_claim' => $claim->canClaim(),
            'claim_count' => $claim->claim_count,
            'remaining_claims' => $promo->claim_limit_per_user 
                ? max(0, $promo->claim_limit_per_user - $claim->claim_count)
                : null,
        ];
    }

    /**
     * Track user spending towards promotions
     */
    public function trackSpending(string $username, float $amount)
    {
        $activePromos = $this->getActivePromotions();

        foreach ($activePromos as $promo) {
            try {
                $claim = PromotionClaim::firstOrCreate(
                    [
                        'promotion_id' => $promo->id,
                        'username' => $username,
                    ],
                    [
                        'total_spent_during_promo' => 0
                    ]
                );
                
                // Handle NULL values by coalescing to 0
                $previousAmount = $claim->total_spent_during_promo ?? 0;
                
                // Explicitly set the new amount (handles NULL values properly)
                $claim->total_spent_during_promo = $previousAmount + $amount;
                $claim->save();
                
                // Refresh to get the saved value
                $claim->refresh();
                
                Log::info("Promotion spending tracked", [
                    'promotion_id' => $promo->id,
                    'username' => $username,
                    'previous_amount' => $previousAmount,
                    'new_amount' => $claim->total_spent_during_promo,
                    'min_amount' => $promo->min_amount,
                    'will_trigger_notification' => ($previousAmount < $promo->min_amount && $claim->total_spent_during_promo >= $promo->min_amount)
                ]);
                
                if ($claim && $previousAmount < $promo->min_amount && $claim->total_spent_during_promo >= $promo->min_amount) {
                    // Mark as eligible when threshold is reached
                    $claim->claimable_at = now();
                    $claim->save();
                    
                    // Clear cache to reflect updated eligibility
                    Cache::forget('active_promotions');
                    
                    // Get eligible users count
                    $eligibleCount = PromotionClaim::where('promotion_id', $promo->id)
                        ->where('total_spent_during_promo', '>=', $promo->min_amount)
                        ->count();
                    
                    Log::info("User {$username} reached promotion #{$promo->id} goal - now {$eligibleCount} eligible users");
                    
                    // Send Discord notification for goal reached
                    $this->discordWebhook->sendNotification(
                        'promotion.claimed',
                        $this->discordWebhook->buildPromotionGoalReachedPayload($promo, $claim, $username, $eligibleCount)
                    );
                }
            } catch (\Exception $e) {
                Log::error("Failed to track spending for promotion {$promo->id}: " . $e->getMessage());
                // Continue with other promotions even if one fails
                continue;
            }
        }

        Log::info("Tracked ${amount} spending for user {$username} across " . $activePromos->count() . " promotions");
    }

    /**
     * Claim a promotion reward
     */
    public function claimReward(Promotion $promo, string $username)
    {
        return DB::transaction(function() use ($promo, $username) {
            $claim = PromotionClaim::lockForUpdate()
                ->where('promotion_id', $promo->id)
                ->where('username', $username)
                ->first();

            if (!$claim) {
                throw new \Exception("No claim record found for this promotion.");
            }

            if (!$claim->canClaim()) {
                throw new \Exception("Cannot claim this promotion. Check eligibility requirements.");
            }

            // Mark as claimed in-game (server will handle actual reward distribution)
            $claim->claim_count++;
            $claim->last_claimed_at = now();
            $claim->claimed_ingame = 1;
            $claim->save();

            // Increment global claim counter
            if ($promo->global_claim_limit) {
                $promo->increment('claimed_global');
            }

            // If recurrent type, reset progress for next claim
            if ($promo->bonus_type === 'recurrent') {
                $claim->decrement('total_spent_during_promo', $promo->min_amount);
            }

            // Clear cache
            Cache::forget('active_promotions');

            Log::info("User {$username} claimed promotion #{$promo->id}: {$promo->title}");

            $this->discordWebhook->sendNotification(
                'promotion.claimed',
                $this->discordWebhook->buildPromotionClaimedPayload($promo, $claim, $username)
            );

            return [
                'success' => true,
                'message' => 'Promotion claimed successfully! Rewards will be available in-game.',
                'rewards' => $promo->reward_items,
                'claim_count' => $claim->claim_count,
            ];
        });
    }

    /**
     * Check if user can claim a specific promotion
     */
    public function canClaim(Promotion $promo, string $username)
    {
        $claim = PromotionClaim::where('promotion_id', $promo->id)
            ->where('username', $username)
            ->first();

        if (!$claim) {
            return false;
        }

        return $claim->canClaim();
    }

    /**
     * Expire old promotions (called by scheduler)
     */
    public function expireOldPromotions()
    {
        $expired = Promotion::where('is_active', true)
            ->where('end_at', '<', now())
            ->update(['is_active' => false]);

        if ($expired > 0) {
            Cache::forget('active_promotions');
            Log::info("Expired {$expired} promotions");
        }

        return $expired;
    }

    /**
     * Create a new promotion
     */
    public function createPromotion(array $data)
    {
        return DB::transaction(function() use ($data) {
            $promotion = Promotion::create($data);
            $cachedId = $promotion->id;
            
            // Find the promotion we just created by most recent creation
            $actualPromotion = Promotion::where('title', $data['title'])
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            
            if (!$actualPromotion) {
                throw new \Exception('Promotion created but could not be found in database');
            }
            
            // If the ID changed, update promotion_claims to use the correct ID
            if ($cachedId != $actualPromotion->id) {
                Log::warning("Promotion ID mismatch detected and fixed", [
                    'cached_id' => $cachedId,
                    'actual_id' => $actualPromotion->id
                ]);
                
                // Update all promotion claims with the correct promotion_id
                DB::table('promotion_claims')
                    ->where('promotion_id', $cachedId)
                    ->update(['promotion_id' => $actualPromotion->id]);
            }
            
            Cache::forget('active_promotions');
            
            $this->discordWebhook->sendNotification(
                'promotion.created',
                $this->discordWebhook->buildPromotionCreatedPayload($actualPromotion)
            );
            
            Log::info("Promotion created and webhook sent", ['promotion_id' => $actualPromotion->id]);
            
            return $actualPromotion;
        });
    }

    /**
     * Update an existing promotion
     */
    public function updatePromotion(Promotion $promotion, array $data)
    {
        $promotion->update($data);
        
        Cache::forget('active_promotions');
        
        return $promotion;
    }

    /**
     * Delete a promotion
     */
    public function deletePromotion(Promotion $promotion)
    {
        $promotion->delete();
        
        Cache::forget('active_promotions');
    }

    /**
     * Get statistics for a promotion
     */
    public function getPromotionStats(Promotion $promo)
    {
        $totalClaims = $promo->claims()->sum('claim_count');
        $uniqueClaimers = $promo->claims()->where('claim_count', '>', 0)->count();
        $totalSpent = $promo->claims()->sum('total_spent_during_promo');
        $eligibleUsers = $promo->claims()
            ->where('total_spent_during_promo', '>=', $promo->min_amount)
            ->count();

        return [
            'total_claims' => $totalClaims,
            'unique_claimers' => $uniqueClaimers,
            'total_spent' => $totalSpent,
            'eligible_users' => $eligibleUsers,
            'global_claims' => $promo->claimed_global,
            'global_limit' => $promo->global_claim_limit,
        ];
    }
}
