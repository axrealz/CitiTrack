<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * The separate Low Stock page was merged into Products (Stock Status column and
 * filter). This route is kept so old links and bookmarks still work.
 */
class LowStockController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('stocker.products.index', ['stock_status' => 'low']);
    }
}