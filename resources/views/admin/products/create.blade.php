@extends('layouts.admin')

@section('title', 'Add Product')

@section('content')

<x-page-header title="Add Product" subtitle="Create a new item in the catalog" />

<form method="POST" action="{{ route('admin.products.store') }}" class="max-w-3xl">
    @csrf

    <x-section-card title="Product Details">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <x-form-input name="name" label="Product Name" :value="old('name')" required />

            <x-form-input name="sku" label="SKU (optional)" :value="old('sku')" hint="Leave blank for auto or custom code" />

            <x-form-select
                name="category_id"
                label="Category"
                :options="$categories->pluck('name', 'id')->toArray()"
                :value="old('category_id')"
                required
            />

            <x-form-select
                name="supplier_id"
                label="Preferred Supplier (optional)"
                :options="$suppliers->pluck('name', 'id')->toArray()"
                :value="old('supplier_id')"
                placeholder="-- None --"
            />

            <x-form-input name="cost" label="Cost (₱)" type="number" step="0.01" min="0" :value="old('cost', 0)" required />

            <x-form-input name="price" label="Selling Price (₱)" type="number" step="0.01" min="0" :value="old('price', 0)" required />

            <x-form-input name="stock_qty" label="Initial Stock" type="number" min="0" :value="old('stock_qty', 0)" required />

            <x-form-input name="reorder_level" label="Reorder Level" type="number" min="0" :value="old('reorder_level', 5)" required
                          hint="Alert fires when stock falls to or below this number" />

            <div class="md:col-span-2">
                <x-form-textarea name="description" label="Description" :value="old('description')" />
            </div>

            <div class="md:col-span-2">
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                           class="rounded border-slate-500 text-amber-600 focus:ring-amber-600">
                    <span class="ml-2 text-sm text-slate-700">Active</span>
                </label>
            </div>

        </div>
    </x-section-card>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="btn-primary">Save Product</button>
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">Cancel</a>
    </div>
</form>

@endsection