@extends('layouts.admin')

@section('title', 'Staff Activity Report')

@section('content')

<x-page-header title="Staff Activity Report" subtitle="Transactions processed per staff member">
    <x-slot:actions>
        <a href="{{ route('admin.export.staff', ['from' => $from, 'to' => $to]) }}" target="_blank" class="btn-secondary">🖨 Print / PDF</a>
    </x-slot:actions>
</x-page-header>

<form method="GET" class="card p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
            <label class="label">From</label>
            <input type="date" name="from" value="{{ $from }}" class="input">
        </div>
        <div>
            <label class="label">To</label>
            <input type="date" name="to" value="{{ $to }}" class="input">
        </div>
        <div class="flex items-end gap-2 md:col-span-2">
            <button type="submit" class="btn-primary">Apply</button>
        </div>
    </div>
</form>

@if($staff->isEmpty())
    <x-empty-state title="No staff found" />
@else
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Role</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Transactions</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Total Sales</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($staff as $person)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $person->name }}</td>
                        <td class="px-4 py-3 text-sm">
                            @php
                                $roleColors = ['admin' => 'indigo', 'cashier' => 'emerald', 'stocker' => 'sky'];
                            @endphp
                            <x-badge :color="$roleColors[$person->role] ?? 'slate'">
                                {{ ucfirst($person->role) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-right text-sm">{{ $person->transactions_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium">
                            <x-money :amount="$person->transactions_sum_total ?? 0" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection