@extends('layouts.admin')

@section('title', 'Edit Supplier')

@section('content')

<x-page-header title="Edit Supplier" :subtitle="$supplier->name" />

<form method="POST" action="{{ route('admin.suppliers.update', $supplier) }}" class="max-w-3xl">
    @csrf
    @method('PUT')

    <x-section-card title="Supplier Details">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="md:col-span-2">
                <x-form-input name="name" label="Supplier Name" :value="old('name', $supplier->name)" required />
            </div>

            <x-form-input name="contact_person" label="Contact Person" :value="old('contact_person', $supplier->contact_person)" />
            <x-form-input name="phone" label="Phone" :value="old('phone', $supplier->phone)" />
            <x-form-input name="email" label="Email" type="email" :value="old('email', $supplier->email)" />
            <x-form-input name="city" label="City" :value="old('city', $supplier->city)" />

            <div class="md:col-span-2">
                <x-form-textarea name="address" label="Address" :value="old('address', $supplier->address)" />
            </div>

            <div class="md:col-span-2">
                <x-form-textarea name="notes" label="Notes (optional)" :value="old('notes', $supplier->notes)" />
            </div>

            <div class="md:col-span-2">
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active))
                           class="rounded border-slate-500 text-amber-600 focus:ring-amber-600">
                    <span class="ml-2 text-sm text-slate-700">Active</span>
                </label>
            </div>

        </div>
    </x-section-card>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="btn-primary">Update Supplier</button>
        <a href="{{ route('admin.suppliers.index') }}" class="btn-secondary">Cancel</a>
    </div>
</form>

@endsection