<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Services\PromotionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    protected $promotionManager;

    public function __construct(PromotionManager $promotionManager)
    {
        $this->promotionManager = $promotionManager;
    }

    public function index()
    {
        $promotions = $this->promotionManager->getAllPromotions();
        
        return view('admin.promotions.index', compact('promotions'));
    }

    public function create()
    {
        return view('admin.promotions.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:128',
            'description' => 'required|string',
            'reward_items' => 'required|array|min:1',
            'reward_items.*.item_id' => 'required|integer',
            'reward_items.*.item_amount' => 'required|integer|min:1',
            'reward_items.*.item_name' => 'required|string',
            'min_amount' => 'required|numeric|min:0.01',
            'bonus_type' => 'required|in:single,recurrent',
            'claim_limit_per_user' => 'nullable|integer|min:1',
            'global_claim_limit' => 'nullable|integer|min:1',
            'start_at' => 'required|date|after_or_equal:now',
            'end_at' => 'required|date|after:start_at',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $promotion = $this->promotionManager->createPromotion($request->all());

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion created successfully!');
    }

    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:128',
            'description' => 'required|string',
            'reward_items' => 'required|array|min:1',
            'reward_items.*.item_id' => 'required|integer',
            'reward_items.*.item_amount' => 'required|integer|min:1',
            'reward_items.*.item_name' => 'required|string',
            'min_amount' => 'required|numeric|min:0.01',
            'bonus_type' => 'required|in:single,recurrent',
            'claim_limit_per_user' => 'nullable|integer|min:1',
            'global_claim_limit' => 'nullable|integer|min:1',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->promotionManager->updatePromotion($promotion, $request->all());

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion updated successfully!');
    }

    public function destroy(Promotion $promotion)
    {
        $this->promotionManager->deletePromotion($promotion);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion deleted successfully!');
    }

    public function show(Promotion $promotion)
    {
        $stats = $this->promotionManager->getPromotionStats($promotion);
        $claims = $promotion->claims()
            ->with('promotion')
            ->orderBy('total_spent_during_promo', 'desc')
            ->paginate(50);

        return view('admin.promotions.show', compact('promotion', 'stats', 'claims'));
    }

    public function toggleActive(Promotion $promotion)
    {
        $promotion->update(['is_active' => !$promotion->is_active]);
        
        return redirect()->back()
            ->with('success', 'Promotion status updated!');
    }
}
