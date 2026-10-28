@extends('layouts.admin')

@section('title', 'Add Staff')

@section('content')

<x-page-header title="Add Staff Account" subtitle="Create a cashier, stocker, or admin" />

<form method="POST" action="{{ route('admin.users.store') }}" class="max-w-2xl">
    @csrf

    <x-section-card title="Account Details">
        <div class="space-y-4">

            <x-form-input name="name" label="Full Name" :value="old('name')" required />

            <x-form-input name="email" label="Email Address" type="email" :value="old('email')" required />

            <x-form-select
                name="role"
                label="Role"
                :options="['admin' => 'Admin', 'cashier' => 'Cashier', 'stocker' => 'Stocker']"
                :value="old('role', 'cashier')"
                required
            />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-input name="password" label="Password" type="password" required
                              hint="Minimum 8 characters" />
                <x-form-input name="password_confirmation" label="Confirm Password" type="password" required />
            </div>

            <p class="text-xs text-slate-500">
                The account will be active immediately and can log in with the credentials above.
            </p>

        </div>
    </x-section-card>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="btn-primary">Create Account</button>
        <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
    </div>
</form>

@endsection