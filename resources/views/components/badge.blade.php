@props(['color' => 'slate', 'size' => 'sm'])

@php
    $palette = [
        'slate'   => 'bg-slate-100 text-slate-700 border-slate-200',
        'green'   => 'bg-green-100 text-green-700 border-green-200',
        'red'     => 'bg-red-100 text-red-700 border-red-200',
        'amber'   => 'bg-amber-100 text-amber-700 border-amber-200',
        'blue'    => 'bg-blue-100 text-blue-700 border-blue-200',
        'indigo'  => 'bg-indigo-100 text-indigo-700 border-indigo-200',
        'sky'     => 'bg-sky-100 text-sky-700 border-sky-200',
        'emerald' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
    ];

    $sizes = [
        'xs' => 'text-xs px-2 py-0.5',
        'sm' => 'text-xs px-2.5 py-0.5',
        'md' => 'text-sm px-3 py-1',
    ];

    $classes = ($palette[$color] ?? $palette['slate']) . ' ' . ($sizes[$size] ?? $sizes['sm']);
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium rounded-full border {$classes}"]) }}>
    {{ $slot }}
</span>