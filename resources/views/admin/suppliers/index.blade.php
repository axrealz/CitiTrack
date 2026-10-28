@extends('layouts.admin')

@section('title', 'Suppliers')

@section('content')

<x-page-header title="Suppliers" subtitle="Manage your product sources">
    <x-slot:actions>
        <a href="{{ route('admin.suppliers.create') }}" class="btn-primary">+ Add Supplier</a>
    </x-slot:actions>
</x-page-header>

<form method="GET" class="card p-4 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2">
            <x-search-input name="q" placeholder="Search by name, contact, or city..." />
        </div>
        <div class="md:col-span-2 flex gap-2">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('admin.suppliers.index') }}" class="btn-secondary">Reset</a>
        </div>
    </div>
</form>

@if($suppliers->isEmpty())
    <x-empty-state title="No suppliers found" message="Add your first supplier to track deliveries." />
@else
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Contact</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">City</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Products</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($suppliers as $supplier)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800">{{ $supplier->name }}</div>
                            @if($supplier->contact_person)
                                <div class="text-xs text-slate-500">{{ $supplier->contact_person }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            @if($supplier->phone)
                                <div>{{ $supplier->phone }}</div>
                            @endif
                            @if($supplier->email)
                                <div class="text-xs text-slate-500">{{ $supplier->email }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $supplier->city ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <x-badge color="indigo">{{ $supplier->products_count }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($supplier->is_active)
                                <x-badge color="green">Active</x-badge>
                            @else
                                <x-badge color="slate">Inactive</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.suppliers.show', $supplier) }}"
                               class="text-sky-700 hover:text-sky-800 text-sm font-medium">View</a>
                            <a href="{{ route('admin.suppliers.edit', $supplier) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium ml-3">Edit</a>
                            <span class="ml-3">
                                <x-delete-button :action="route('admin.suppliers.destroy', $supplier)" entity="supplier" :subject="$supplier->name" />
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-pagination :paginator="$suppliers" />
@endif

@endsection