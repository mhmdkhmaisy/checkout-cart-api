<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::active()
            ->select('id', 'product_name', 'item_id', 'qty_unit', 'price', 'is_active')
            ->orderBy('product_name')
            ->get();

        return response()->json([
            'products' => $products
        ]);
    }
}