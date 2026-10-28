@extends('layouts.admin')

@section('title', 'Stock Adjustments')

@section('content')

<x-page-header title="Stock Adjustments" subtitle="Review stocker adjustment requests" />

<form method="GET" class="card p-4 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <x-filter-select
            name="status"
            :options="['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']"
            placeholder="All Statuses"
        />
        <div class="md:col-span-3 flex gap-2">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('admin.adjustments.index') }}" class="btn-secondary">Reset</a>
        </div>
    </div>
</form>

@if($adjustments->isEmpty())
    <x-empty-state title="No adjustment requests" message="Requests submitted by stockers will appear here." />
@else
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Requested by</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Old → New</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Difference</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($adjustments as $adj)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">
                            {{ $adj->product->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">
                            {{ $adj->requestedBy->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <span class="text-slate-500">{{ $adj->old_qty }}</span>
                            →
                            <span class="font-medium">{{ $adj->new_qty }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-mono
                            {{ $adj->difference > 0 ? 'text-emerald-700' : 'text-red-600' }}">
                            {{ $adj->difference > 0 ? '+' : '' }}{{ $adj->difference }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColors = ['pending' => 'amber', 'approved' => 'green', 'rejected' => 'red'];
                            @endphp
                            <x-badge :color="$statusColors[$adj->status] ?? 'slate'">
                                {{ ucfirst($adj->status) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.adjustments.show', $adj) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-pagination :paginator="$adjustments" />
@endif

@endsection