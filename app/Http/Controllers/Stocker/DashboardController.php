<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentRequest;
use App\Models\Product;
use App\Models\StockLog;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        // ── Stats ──
        $totalProducts   = Product::active()->count();
        $lowStockCount   = Product::active()->lowStock()->count();
        $todayStockIns   = StockLog::where('type', 'stock_in')
            ->whereDate('created_at', $today)
            ->count();
        $pendingAdjustments = AdjustmentRequest::where('requested_by', auth()->id())
            ->pending()
            ->count();

        // ── Low stock products (top 5) ──
        $lowStockProducts = Product::active()
            ->lowStock()
            ->with(['category', 'supplier'])
            ->orderBy('stock_qty')
            ->take(5)
            ->get();

        // ── Recent stock-in entries ──
        $recentStockIns = StockLog::with(['product', 'supplier', 'user'])
            ->where('type', 'stock_in')
            ->latest()
            ->take(5)
            ->get();

        return view('stocker.dashboard', compact(
            'totalProducts',
            'lowStockCount',
            'todayStockIns',
            'pendingAdjustments',
            'lowStockProducts',
            'recentStockIns',
        ));
    }
}