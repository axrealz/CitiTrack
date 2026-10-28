@extends('layouts.stocker')

@section('title', 'My Adjustment Requests')

@section('content')

<x-page-header title="Adjustment Requests" subtitle="Track your stock adjustment submissions">
    <x-slot:actions>
        <a href="{{ route('stocker.adjustments.create') }}" class="btn-primary">+ New Request</a>
    </x-slot:actions>
</x-page-header>

@if($adjustments->isEmpty())
    <x-empty-state title="No adjustment requests yet" message="Submit a request when physical count doesn't match the system.">
        <a href="{{ route('stocker.adjustments.create') }}" class="btn-primary">+ New Request</a>
    </x-empty-state>
@else
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Product</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Old → New</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Diff</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Submitted</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Admin Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($adjustments as $adj)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-medium text-slate-800">
                            {{ $adj->product->name ?? '—' }}
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
                        <td class="px-4 py-3 text-xs text-slate-500">
                            {{ $adj->created_at->diffForHumans() }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $sc = ['pending' => 'amber', 'approved' => 'green', 'rejected' => 'red'];
                            @endphp
                            <x-badge :color="$sc[$adj->status] ?? 'slate'">
                                {{ ucfirst($adj->status) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            {{ $adj->rejection_reason ?? '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-pagination :paginator="$adjustments" />
@endif

@endsection