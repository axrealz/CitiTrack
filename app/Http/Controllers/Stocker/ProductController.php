<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * List all products (read-only for stocker).
     */
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'supplier']);

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($category = $request->get('category')) {
            $query->where('category_id', $category);
        }

        if ($request->get('low_stock') === '1') {
            $query->lowStock();
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('stocker.products', compact('products'));
    }

    /**
     * Show a product's details and stock history.
     */
    public function show(Product $product): View
    {
        $product->load(['category', 'supplier', 'stockLogs.user', 'stockLogs.supplier']);

        return view('stocker.products', [
            'products'      => collect(),
            'product'       => $product,
            'stockHistory'  => $product->stockLogs()->latest()->take(20)->get(),
        ]);
    }
}