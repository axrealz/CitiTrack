<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentRequest;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VoidRequest;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        // ── Stat cards ──
        $todaySales       = Transaction::completed()->whereDate('created_at', $today)->sum('total');
        $todayTransactions = Transaction::completed()->whereDate('created_at', $today)->count();
        $monthlyRevenue   = Transaction::completed()
            ->whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->sum('total');
        $totalProducts    = Product::active()->count();
        $lowStockCount    = Product::active()->lowStock()->count();
        $totalStaff       = User::active()->count();

        // ── Pending requests ──
        $pendingVoids       = VoidRequest::pending()->count();
        $pendingAdjustments = AdjustmentRequest::pending()->count();

        // ── Recent activity (timeline) ──
        $recentLogs = AuditLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // ── Recent transactions ──
        $recentTransactions = Transaction::with('cashier')
            ->completed()
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'todaySales',
            'todayTransactions',
            'monthlyRevenue',
            'totalProducts',
            'lowStockCount',
            'totalStaff',
            'pendingVoids',
            'pendingAdjustments',
            'recentLogs',
            'recentTransactions',
        ));
    }
}