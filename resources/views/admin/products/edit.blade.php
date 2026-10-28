@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')

<x-page-header title="Edit Product" :subtitle="$product->name" />

<form method="POST" action="{{ route('admin.products.update', $product) }}" class="max-w-3xl">
    @csrf
    @method('PUT')

    <x-section-card title="Product Details">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <x-form-input name="name" label="Product Name" :value="old('name', $product->name)" required />

            <x-form-input name="sku" label="SKU (optional)" :value="old('sku', $product->sku)" />

            <x-form-select
                name="category_id"
                label="Category"
                :options="$categories->pluck('name', 'id')->toArray()"
                :value="old('category_id', $product->category_id)"
                required
            />

            <x-form-select
                name="supplier_id"
                label="Preferred Supplier (optional)"
                :options="$suppliers->pluck('name', 'id')->toArray()"
                :value="old('supplier_id', $product->supplier_id)"
                placeholder="-- None --"
            />

            <x-form-input name="cost" label="Cost (₱)" type="number" step="0.01" min="0" :value="old('cost', $product->cost)" required />

            <x-form-input name="price" label="Selling Price (₱)" type="number" step="0.01" min="0" :value="old('price', $product->price)" required />

            <x-form-input name="stock_qty" label="Stock Quantity" type="number" min="0" :value="old('stock_qty', $product->stock_qty)" required />

            <x-form-input name="reorder_level" label="Reorder Level" type="number" min="0" :value="old('reorder_level', $product->reorder_level)" required />

            <div class="md:col-span-2">
                <x-form-textarea name="description" label="Description" :value="old('description', $product->description)" />
            </div>

            <div class="md:col-span-2">
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))
                           class="rounded border-slate-500 text-amber-600 focus:ring-amber-600">
                    <span class="ml-2 text-sm text-slate-700">Active</span>
                </label>
            </div>

        </div>
    </x-section-card>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="btn-primary">Update Product</button>
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">Cancel</a>
    </div>
</form>

@endsection