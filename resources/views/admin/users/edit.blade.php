@extends('layouts.admin')

@section('title', 'Edit Staff')

@section('content')

<x-page-header title="Edit Staff" :subtitle="$user->name" />

@php
    // The confirmation dialog only appears when role, active status or password changes.
    $isSelf = $user->id === auth()->id();
    $initialState = ['role' => $user->role, 'password' => ''];
    if (! $isSelf) {
        $initialState['is_active'] = $user->is_active ? '1' : '0';
    }
@endphp

<form method="POST" action="{{ route('admin.users.update', $user) }}" class="max-w-2xl">
    @csrf
    @method('PUT')

    <x-section-card title="Account Details">
        <div class="space-y-4">

            <x-form-input name="name" label="Full Name" :value="old('name', $user->name)" required />

            <x-form-input name="email" label="Email Address" type="email" :value="old('email', $user->email)" required />

            <x-form-select
                name="role"
                label="Role"
                :options="['admin' => 'Admin', 'cashier' => 'Cashier', 'stocker' => 'Stocker']"
                :value="old('role', $user->role)"
                required
            />

            <div class="border-t border-slate-200 pt-4">
                <p class="text-sm font-medium text-slate-700 mb-3">Change Password</p>
                <p class="text-xs text-slate-500 mb-3">Leave blank to keep the current password.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form-input name="password" label="New Password" type="password"
                                  hint="Minimum 8 characters" />
                    <x-form-input name="password_confirmation" label="Confirm Password" type="password" />
                </div>
            </div>

            <div class="border-t border-slate-200 pt-4">
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           @checked(old('is_active', $user->is_active))
                           @disabled($user->id === auth()->id())
                           class="rounded border-slate-500 text-amber-600 focus:ring-amber-600">
                    <span class="ml-2 text-sm text-slate-700">Active account</span>
                </label>
                @if($user->id === auth()->id())
                    <p class="text-xs text-amber-700 mt-2">You cannot deactivate your own account.</p>
                @endif
            </div>

        </div>
    </x-section-card>

    <div class="mt-6 flex gap-3">
        <x-confirm-submit
            label="Update Account"
            class="btn-primary"
            variant="primary"
            title="Save changes to this account?"
            message="These changes take effect immediately."
            :subject="$user->name"
            confirmLabel="Confirm Changes"
            :initial="$initialState"
        >
            <ul class="list-disc pl-5 space-y-1">
                <li x-show="vals.role !== initial.role">
                    Role: <strong>{{ ucfirst($user->role) }}</strong> → <strong x-text="labels.role"></strong>
                </li>
                @unless($isSelf)
                    <li x-show="vals.is_active !== initial.is_active">
                        Account status: <strong>{{ $user->is_active ? 'Active' : 'Inactive' }}</strong>
                        → <strong x-text="vals.is_active === '1' ? 'Active' : 'Inactive'"></strong>
                        <span x-show="vals.is_active === '0'">(they will no longer be able to log in)</span>
                    </li>
                @endunless
                <li x-show="(vals.password ?? '') !== ''">The password will be reset.</li>
            </ul>
        </x-confirm-submit>
        <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
    </div>
</form>

@endsection