<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user  = auth()->user();
        $today = Carbon::today();

        // Today's stats for THIS cashier only
        $todaySales        = Transaction::completed()
            ->where('cashier_id', $user->id)
            ->whereDate('created_at', $today)
            ->sum('total');

        $todayTransactions = Transaction::completed()
            ->where('cashier_id', $user->id)
            ->whereDate('created_at', $today)
            ->count();

        $monthlySales = Transaction::completed()
            ->where('cashier_id', $user->id)
            ->whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->sum('total');

        // Recent transactions by this cashier
        $recentTransactions = Transaction::with('items')
            ->where('cashier_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Pending void requests made by this cashier
        $pendingVoids = \App\Models\VoidRequest::where('requested_by', $user->id)
            ->pending()
            ->count();

        return view('cashier.dashboard', compact(
            'todaySales',
            'todayTransactions',
            'monthlySales',
            'recentTransactions',
            'pendingVoids',
        ));
    }
}