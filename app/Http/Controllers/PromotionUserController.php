<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Services\PromotionManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PromotionUserController extends Controller
{
    protected $promotionManager;

    public function __construct(PromotionManager $promotionManager)
    {
        $this->promotionManager = $promotionManager;
    }

    public function getActive(): JsonResponse
    {
        $promotions = $this->promotionManager->getActivePromotions();
        
        return response()->json([
            'success' => true,
            'promotions' => $promotions
        ]);
    }

    public function getUserProgress(string $username): JsonResponse
    {
        $progress = $this->promotionManager->evaluateUserProgress($username);
        
        return response()->json([
            'success' => true,
            'username' => $username,
            'progress' => $progress
        ]);
    }

    public function claim(Request $request, Promotion $promotion): JsonResponse
    {
        $request->validate([
            'username' => 'required|string|max:50|regex:/^[A-Za-z0-9_]+$/'
        ]);

        $username = $request->input('username');

        try {
            $result = $this->promotionManager->claimReward($promotion, $username);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
