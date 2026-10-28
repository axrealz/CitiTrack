<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class LowStockController extends Controller
{
    public function index(): View
    {
        $products = Product::active()
            ->lowStock()
            ->with(['category', 'supplier'])
            ->orderBy('stock_qty')
            ->get();

        return view('stocker.low-stock', compact('products'));
    }
}