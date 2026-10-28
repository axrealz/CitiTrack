@props(['title' => '', 'subtitle' => null])

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions) || ! $slot->isEmpty())
        <div class="flex items-center gap-2">
            {{ $actions ?? '' }}
            {{ $slot }}
        </div>
    @endif
</div>