<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use App\Models\StockLog;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShipmentLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = StockLog::with(['product', 'supplier', 'user'])
            ->where('type', 'stock_in');

        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($supplier = $request->get('supplier')) {
            $query->where('supplier_id', $supplier);
        }

        $logs      = $query->latest()->paginate(20)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('stocker.shipment-logs', compact('logs', 'suppliers'));
    }
}