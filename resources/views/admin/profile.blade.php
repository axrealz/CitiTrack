@extends('layouts.admin')

@section('title', 'Profile')

@section('content')

<x-page-header title="My Profile" subtitle="Manage your account details" />

<div class="max-w-2xl space-y-6">

    <x-section-card title="Profile Information" subtitle="Update your name and email">
        @include('profile.partials.update-profile-information-form')
    </x-section-card>

    <x-section-card title="Update Password" subtitle="Use a strong, unique password">
        @include('profile.partials.update-password-form')
    </x-section-card>

</div>

@endsection