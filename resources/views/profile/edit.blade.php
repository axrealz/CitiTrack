@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

<div class="max-w-3xl mx-auto px-6 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">My Profile</h1>
            <p class="text-sm text-slate-500 mt-1">Manage your account details and security</p>
        </div>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/dashboard') }}"
           class="btn-secondary">
            ← Back
        </a>
    </div>

    <div class="space-y-6">

        <x-section-card title="Profile Information" subtitle="Update your name and email address">
            @include('profile.partials.update-profile-information-form')
        </x-section-card>

        <x-section-card title="Change Password" subtitle="Use a strong, unique password">
            @include('profile.partials.update-password-form')
        </x-section-card>

        <x-section-card title="Delete Account" subtitle="Permanently remove your account">
            @include('profile.partials.delete-user-form')
        </x-section-card>

    </div>

</div>

@endsection