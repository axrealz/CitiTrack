@extends('layouts.admin')

@section('title', 'Add Supplier')

@section('content')

<x-page-header title="Add Supplier" subtitle="Create a new product source" />

<form method="POST" action="{{ route('admin.suppliers.store') }}" class="max-w-3xl">
    @csrf

    <x-section-card title="Supplier Details">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="md:col-span-2">
                <x-form-input name="name" label="Supplier Name" :value="old('name')" required />
            </div>

            <x-form-input name="contact_person" label="Contact Person" :value="old('contact_person')" />
            <x-form-input name="phone" label="Phone" :value="old('phone')" />
            <x-form-input name="email" label="Email" type="email" :value="old('email')" />
            <x-form-input name="city" label="City" :value="old('city')" />

            <div class="md:col-span-2">
                <x-form-textarea name="address" label="Address" :value="old('address')" />
            </div>

            <div class="md:col-span-2">
                <x-form-textarea name="notes" label="Notes (optional)" :value="old('notes')" />
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
        <button type="submit" class="btn-primary">Save Supplier</button>
        <a href="{{ route('admin.suppliers.index') }}" class="btn-secondary">Cancel</a>
    </div>
</form>

@endsection