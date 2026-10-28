@extends('layouts.cashier')

@section('title', 'My Void Requests')

@section('content')

<x-page-header title="Void Requests" subtitle="Track your submitted void requests" />

@if($voids->isEmpty())
    <x-empty-state title="No void requests yet" message="You can request a void from the sales history page.">
        <a href="{{ route('cashier.sales-history') }}" class="btn-primary">Go to Sales History</a>
    </x-empty-state>
@else
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Transaction</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Submitted</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Admin Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($voids as $void)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs">
                            {{ $void->transaction->reference_no ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600 max-w-xs truncate">{{ $void->reason }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $void->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColors = ['pending' => 'amber', 'approved' => 'green', 'rejected' => 'red'];
                            @endphp
                            <x-badge :color="$statusColors[$void->status] ?? 'slate'">
                                {{ ucfirst($void->status) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            {{ $void->rejection_reason ?? '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-pagination :paginator="$voids" />
@endif

@endsection