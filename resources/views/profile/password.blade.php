@extends('layouts.app')

@section('title', 'Change Password')

@section('content')

<div class="max-w-2xl mx-auto px-6 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Change Password</h1>
            <p class="text-sm text-slate-500 mt-1">Use a strong, unique password</p>
        </div>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/dashboard') }}"
           class="btn-secondary">
            ← Back
        </a>
    </div>

    <x-section-card>
        @include('profile.partials.update-password-form')
    </x-section-card>

</div>

@endsection