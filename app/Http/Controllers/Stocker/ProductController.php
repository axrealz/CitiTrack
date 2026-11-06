<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Search;
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

        Search::apply($query, $request->get('q'), ['name', 'sku']);

        if ($category = $request->get('category')) {
            $query->where('category_id', $category);
        }

        if ($request->get('low_stock') === '1') {
            $query->lowStock();   // old links/bookmarks
        }

        // Stock Status filter: in / low / out (most urgent first for low and out)
        $stockStatus = $request->get('stock_status');
        $query->withStockStatus($stockStatus);

        if (in_array($stockStatus, ['low', 'out'], true)) {
            $query->orderBy('stock_qty');
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