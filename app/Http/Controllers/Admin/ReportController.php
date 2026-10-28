<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports.index');
    }

    public function products(Request $request): View
    {
        $products = Product::with(['category', 'supplier'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.reports.products', compact('products'));
    }

    public function staff(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $staff = User::whereIn('role', ['cashier', 'stocker'])
            ->withCount(['transactions' => function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            }])
            ->withSum(['transactions' => function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            }], 'total')
            ->get();

        return view('admin.reports.staff', compact('staff', 'from', 'to'));
    }

    public function financial(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $base = Transaction::completed()
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        $revenue = (clone $base)->sum('total');
        $salesCount = (clone $base)->count();
        $cashTotal  = (clone $base)->where('payment_method', 'cash')->sum('total');
        $gcashTotal = (clone $base)->where('payment_method', 'gcash')->sum('total');

        // Profit = sum of (unit_price - unit_cost) * qty
        $profit = TransactionItem::whereHas('transaction', function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                  ->where('status', 'completed');
            })
            ->selectRaw('SUM((unit_price - unit_cost) * qty) as total_profit')
            ->value('total_profit') ?? 0;

        return view('admin.reports.financial', compact(
            'from', 'to', 'revenue', 'salesCount', 'cashTotal', 'gcashTotal', 'profit'
        ));
    }

    public function dailySales(Request $request): View
    {
        $from = $request->get('from', now()->subDays(6)->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $sales = Transaction::completed()
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.reports.daily-sales', compact('sales', 'from', 'to'));
    }

    public function itemSales(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $items = TransactionItem::whereHas('transaction', function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                  ->where('status', 'completed');
            })
            ->select(
                'product_id',
                'product_name',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(subtotal) as total_sales')
            )
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_sales')
            ->paginate(20);

        return view('admin.reports.item-sales', compact('items', 'from', 'to'));
    }

    public function inventorySummary(): View
    {
        $products = Product::with(['category', 'supplier'])
            ->orderBy('name')
            ->paginate(20);

        $totalStockValue = Product::active()->sum(DB::raw('stock_qty * cost'));

        return view('admin.reports.inventory-summary', compact('products', 'totalStockValue'));
    }

    public function lowStock(): View
    {
        $products = Product::active()
            ->lowStock()
            ->with(['category', 'supplier'])
            ->orderBy('stock_qty')
            ->get();

        return view('admin.reports.low-stock', compact('products'));
    }
}