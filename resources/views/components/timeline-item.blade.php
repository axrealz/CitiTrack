@props(['icon' => null, 'title' => '', 'time' => null, 'description' => null, 'accent' => 'slate'])

@php
    $accents = [
        'slate'   => 'bg-slate-100 text-slate-600',
        'indigo'  => 'bg-indigo-100 text-indigo-600',
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'sky'     => 'bg-sky-100 text-sky-700',
        'amber'   => 'bg-amber-100 text-amber-700',
        'red'     => 'bg-red-100 text-red-700',
    ];
@endphp

<li class="flex gap-3 py-3">
    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $accents[$accent] ?? $accents['slate'] }}">
        @if($icon)
            <span class="text-sm">{!! $icon !!}</span>
        @else
            <span class="w-2 h-2 rounded-full bg-current"></span>
        @endif
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-baseline justify-between gap-3">
            <p class="text-sm font-medium text-slate-800 truncate">{{ $title }}</p>
            @if($time)
                <span class="text-xs text-slate-500 whitespace-nowrap">{{ $time }}</span>
            @endif
        </div>
        @if($description)
            <p class="text-xs text-slate-500 mt-0.5">{{ $description }}</p>
        @endif
        {{ $slot }}
    </div>
</li>