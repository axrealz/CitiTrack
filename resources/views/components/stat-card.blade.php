@props([
    'label'  => '',
    'value'  => '0',
    'icon'   => null,
    'accent' => 'indigo',  // indigo | emerald | sky | amber | red
    'suffix' => null,
])

@php
    $accents = [
        'indigo'  => 'bg-indigo-50 text-indigo-600',
        'emerald' => 'bg-emerald-50 text-emerald-700',
        'sky'     => 'bg-sky-50 text-sky-700',
        'amber'   => 'bg-amber-50 text-amber-700',
        'red'     => 'bg-red-50 text-red-700',
    ];
@endphp

<div class="card p-5 flex items-center gap-4">
    @if($icon)
        <div class="w-12 h-12 rounded-lg flex items-center justify-center {{ $accents[$accent] ?? $accents['indigo'] }}">
            <span class="text-2xl">{!! $icon !!}</span>
        </div>
    @endif
    <div class="min-w-0">
        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</div>
        <div class="text-2xl font-bold text-slate-800 truncate">
            {{ $value }}@if($suffix)<span class="text-lg text-slate-500 ml-1">{{ $suffix }}</span>@endif
        </div>
    </div>
</div>