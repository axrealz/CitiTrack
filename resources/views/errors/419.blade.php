<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>419 — Session Expired</title>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans bg-slate-50 min-h-screen flex items-center justify-center px-6">
    <div class="text-center max-w-md">
        <div class="text-7xl font-bold text-amber-500">419</div>
        <h1 class="text-2xl font-bold text-slate-800 mt-4">Session Expired</h1>
        <p class="text-slate-500 mt-2">Your session has timed out. Please log in again.</p>
        <a href="{{ route('login') }}" class="btn-primary inline-block mt-6">Log in</a>
    </div>
</body>
</html>