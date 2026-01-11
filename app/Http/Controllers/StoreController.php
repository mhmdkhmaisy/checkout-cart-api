<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\StoreAlert;
use App\Services\PromotionManager;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    protected $promotionManager;

    public function __construct(PromotionManager $promotionManager)
    {
        $this->promotionManager = $promotionManager;
    }

    public function index(Request $request): View
    {
        $query = Product::with(['category', 'bundleItems'])
            ->active()
            ->orderBy('sort_order')
            ->orderBy('product_name');

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category_id', $request->category);
        }

        $products = $query->get();
        $categories = Category::where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        // Get active promotions
        $promotions = $this->promotionManager->getActivePromotions();

        // Get active store alerts
        $alerts = StoreAlert::active()->get();
        
        // Get user progress if cart_user session exists
        $userProgress = [];
        if (session('cart_user')) {
            $userProgress = $this->promotionManager->evaluateUserProgress(session('cart_user'));
        }

        return view('store.index', compact('products', 'categories', 'promotions', 'userProgress', 'alerts'));
    }

    public function terms(): View
    {
        return view('store.terms');
    }

    public function setUser(Request $request): JsonResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_ ]+$/']
        ]);

        $username = $request->input('username');
        session(['cart_user' => $username]);

        return response()->json([
            'success' => true,
            'message' => 'User set successfully',
            'username' => $username
        ]);
    }

    public function clearUser(): JsonResponse
    {
        session()->forget('cart_user');
        session()->forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'User cleared successfully'
        ]);
    }

    public function addToCart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1'
        ]);

        $product = Product::with('bundleItems')->findOrFail($request->product_id);
        $quantity = $request->input('quantity', 1);

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->product_name,
                'price' => $product->price,
                'quantity' => $quantity,
                'item_id' => $product->item_id,
                'qty_unit' => $product->qty_unit
            ];
        }

        session(['cart' => $cart]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cart_count' => count($cart),
            'cart' => $cart
        ]);
    }

    public function updateCart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0'
        ]);

        $cart = session()->get('cart', []);
        $productId = $request->product_id;
        $quantity = $request->quantity;

        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $quantity;
            }
        }

        session(['cart' => $cart]);

        $total = array_reduce($cart, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated',
            'cart_count' => count($cart),
            'cart' => $cart,
            'total' => number_format($total, 2)
        ]);
    }

    public function removeFromCart(Request $request, $productId): JsonResponse
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
        }

        session(['cart' => $cart]);

        $total = array_reduce($cart, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        return response()->json([
            'success' => true,
            'message' => 'Product removed from cart',
            'cart_count' => count($cart),
            'cart' => $cart,
            'total' => number_format($total, 2)
        ]);
    }

    public function getCart(): JsonResponse
    {
        $cart = session()->get('cart', []);
        $total = array_reduce($cart, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'cart_count' => count($cart),
            'total' => number_format($total, 2),
            'username' => session('cart_user')
        ]);
    }

    public function clearCart(): JsonResponse
    {
        session()->forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }
}
