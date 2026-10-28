<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Models\AuditLog;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $query = Supplier::withCount('products');

        if ($search = $request->get('q')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
        }

        $suppliers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('admin.suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::create($request->validated());

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'supplier.created',
            'model_type' => Supplier::class,
            'model_id'   => $supplier->id,
            'new_values' => $supplier->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['products', 'stockLogs.product']);

        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $old = $supplier->toArray();
        $supplier->update($request->validated());

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'supplier.updated',
            'model_type' => Supplier::class,
            'model_id'   => $supplier->id,
            'old_values' => $old,
            'new_values' => $supplier->fresh()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'supplier.deleted',
            'model_type' => Supplier::class,
            'model_id'   => $supplier->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted.');
    }
}