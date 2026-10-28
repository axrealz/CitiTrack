<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>403 — Forbidden</title>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans bg-slate-50 min-h-screen flex items-center justify-center px-6">
    <div class="text-center max-w-md">
        <div class="text-7xl font-bold text-amber-500">403</div>
        <h1 class="text-2xl font-bold text-slate-800 mt-4">Access Denied</h1>
        <p class="text-slate-500 mt-2">You don't have permission to view this page.</p>
        <a href="{{ url()->previous() }}" class="btn-secondary inline-block mt-6">← Go back</a>
    </div>
</body>
</html>