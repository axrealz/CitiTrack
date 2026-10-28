<header class="h-14 bg-slate-900 text-white flex items-center px-6 no-print">
    <div class="flex items-center gap-3">
        <div class="font-bold">CitiTrack</div>
    </div>
    <div class="ml-auto text-sm flex gap-4 items-center">
        <span class="text-slate-300">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-red-400 hover:text-red-300 text-xs">Log out</button>
        </form>
    </div>
</header>