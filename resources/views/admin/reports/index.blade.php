@extends('layouts.admin')

@section('title', 'Reports')

@section('content')

<x-page-header title="Reports" subtitle="Analytics and operational insights" />

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

    <a href="{{ route('admin.reports.daily-sales') }}" class="card p-5 hover:shadow-md transition group">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-2xl">📅</div>
            <div>
                <div class="font-semibold text-slate-800 group-hover:text-amber-600">Daily Sales</div>
                <div class="text-xs text-slate-500 mt-1">Totals per day over a date range</div>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.reports.item-sales') }}" class="card p-5 hover:shadow-md transition group">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl">🏆</div>
            <div>
                <div class="font-semibold text-slate-800 group-hover:text-amber-600">Item-Level Sales</div>
                <div class="text-xs text-slate-500 mt-1">Best-selling products by qty and revenue</div>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.reports.products') }}" class="card p-5 hover:shadow-md transition group">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-lg bg-sky-50 text-sky-700 flex items-center justify-center text-2xl">📦</div>
            <div>
                <div class="font-semibold text-slate-800 group-hover:text-amber-600">Products</div>
                <div class="text-xs text-slate-500 mt-1">Full product catalog with pricing</div>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.reports.inventory-summary') }}" class="card p-5 hover:shadow-md transition group">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">📊</div>
            <div>
                <div class="font-semibold text-slate-800 group-hover:text-amber-600">Inventory Summary</div>
                <div class="text-xs text-slate-500 mt-1">Stock value and quantity snapshot</div>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.reports.low-stock') }}" class="card p-5 hover:shadow-md transition group">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-2xl">⚠️</div>
            <div>
                <div class="font-semibold text-slate-800 group-hover:text-amber-600">Low Stock</div>
                <div class="text-xs text-slate-500 mt-1">Items at or below reorder level</div>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.reports.financial') }}" class="card p-5 hover:shadow-md transition group">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-2xl">💰</div>
            <div>
                <div class="font-semibold text-slate-800 group-hover:text-amber-600">Financial</div>
                <div class="text-xs text-slate-500 mt-1">Revenue, profit, cash vs GCash split</div>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.reports.staff') }}" class="card p-5 hover:shadow-md transition group">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl">👥</div>
            <div>
                <div class="font-semibold text-slate-800 group-hover:text-amber-600">Staff Activity</div>
                <div class="text-xs text-slate-500 mt-1">Transactions per cashier</div>
            </div>
        </div>
    </a>

</div>

@endsection