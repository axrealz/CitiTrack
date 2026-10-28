@extends('layouts.admin')

@section('title', $user->name)

@section('content')

<x-page-header :title="$user->name" :subtitle="$user->email" />

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-1 space-y-6">
        <x-section-card title="Account">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Role</dt>
                    <dd>
                        @php
                            $roleColors = ['admin' => 'indigo', 'cashier' => 'emerald', 'stocker' => 'sky'];
                        @endphp
                        <x-badge :color="$roleColors[$user->role] ?? 'slate'">
                            {{ ucfirst($user->role) }}
                        </x-badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Status</dt>
                    <dd>
                        @if($user->is_active)
                            <x-badge color="green">Active</x-badge>
                        @else
                            <x-badge color="red">Inactive</x-badge>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Joined</dt>
                    <dd class="text-slate-800">{{ $user->created_at->format('M d, Y g:i A') }}</dd>
                </div>
                @if($user->email_verified_at)
                    <div>
                        <dt class="text-slate-500">Email Verified</dt>
                        <dd class="text-slate-800">{{ $user->email_verified_at->format('M d, Y') }}</dd>
                    </div>
                @endif
            </dl>
        </x-section-card>

        <x-section-card title="Actions">
            <div class="space-y-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary w-full justify-center">
                    Edit Account
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary w-full justify-center">
                    Back to list
                </a>
            </div>
        </x-section-card>
    </div>

    <div class="lg:col-span-2 space-y-6">

        @if($user->isCashier())
            <x-section-card title="Recent Transactions" subtitle="Last 10 sales processed">
                @if($user->transactions->isEmpty())
                    <p class="text-sm text-slate-500">No transactions yet.</p>
                @else
                    <table class="w-full text-sm">
                        <thead class="text-xs uppercase text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="py-2 text-left">Reference</th>
                                <th class="py-2 text-left">Date</th>
                                <th class="py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($user->transactions->sortByDesc('created_at')->take(10) as $tx)
                                <tr>
                                    <td class="py-2 font-mono text-xs">{{ $tx->reference_no }}</td>
                                    <td class="py-2 text-slate-600">{{ $tx->created_at->format('M d, Y g:i A') }}</td>
                                    <td class="py-2 text-right font-medium"><x-money :amount="$tx->total" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </x-section-card>
        @endif

        @if($user->isStocker())
            <x-section-card title="Recent Stock-Ins" subtitle="Last 10 deliveries recorded">
                @if($user->stockLogs->isEmpty())
                    <p class="text-sm text-slate-500">No stock movements yet.</p>
                @else
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach($user->stockLogs->sortByDesc('created_at')->take(10) as $log)
                            <li class="py-2 flex justify-between gap-3">
                                <div>
                                    <div class="font-medium text-slate-700">{{ $log->product->name ?? '—' }}</div>
                                    <div class="text-xs text-slate-500">{{ $log->created_at->format('M d, Y g:i A') }}</div>
                                </div>
                                <div class="{{ $log->qty > 0 ? 'text-emerald-700' : 'text-red-600' }} font-mono text-sm">
                                    {{ $log->qty > 0 ? '+' : '' }}{{ $log->qty }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-section-card>
        @endif

        <x-section-card title="Recent Activity" subtitle="Last 10 audit log entries">
            @if($user->auditLogs->isEmpty())
                <p class="text-sm text-slate-500">No activity yet.</p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach($user->auditLogs->sortByDesc('created_at')->take(10) as $log)
                        <x-timeline-item
                            :title="$log->action"
                            :time="$log->created_at->diffForHumans()"
                            :description="$log->model_type ? class_basename($log->model_type) : null"
                            accent="{{ str_contains($log->action, 'rejected') || str_contains($log->action, 'deleted') ? 'red' : 'indigo' }}"
                        />
                    @endforeach
                </ul>
            @endif
        </x-section-card>

    </div>

</div>

@endsection