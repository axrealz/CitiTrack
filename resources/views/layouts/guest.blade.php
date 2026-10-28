<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CitiTrack') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-slate-900 antialiased">

    <div class="min-h-screen flex flex-col sm:justify-center items-center
                pt-6 sm:pt-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">

        <div class="mb-6 text-center">
            <h1 class="text-3xl font-bold text-white">CitiTrack</h1>
            <p class="text-sm text-slate-300 mt-1">
                Sales &amp; Inventory Management System
            </p>
        </div>

        <div class="w-full sm:max-w-md mt-4 px-6 py-8 bg-white shadow-lg overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>

        <p class="mt-6 text-xs text-slate-400">
            &copy; {{ date('Y') }} Citi Power Electronic Supply
        </p>
    </div>

</body>
</html>