@extends('layouts.admin')

@section('title', 'Void Requests')

@section('content')

<x-page-header title="Void Requests" subtitle="Review cashier void submissions" />

<form method="GET" class="card p-4 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <x-filter-select
            name="status"
            :options="['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']"
            placeholder="All Statuses"
        />
        <div class="md:col-span-3 flex gap-2">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('admin.voids.index') }}" class="btn-secondary">Reset</a>
        </div>
    </div>
</form>

@if($voids->isEmpty())
    <x-empty-state title="No void requests" message="Void requests submitted by cashiers will appear here." />
@else
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Transaction</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Cashier</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Submitted</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($voids as $void)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs text-slate-700">
                            {{ $void->transaction->reference_no ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">
                            {{ $void->requestedBy->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 max-w-xs truncate">
                            {{ $void->reason }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-500">
                            {{ $void->created_at->diffForHumans() }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColors = ['pending' => 'amber', 'approved' => 'green', 'rejected' => 'red'];
                            @endphp
                            <x-badge :color="$statusColors[$void->status] ?? 'slate'">
                                {{ ucfirst($void->status) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.voids.show', $void) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-pagination :paginator="$voids" />
@endif

@endsection