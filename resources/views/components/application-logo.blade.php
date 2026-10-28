@props(['class' => ''])

<svg {{ $attributes->merge(['class' => $class]) }}
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="2"
     stroke-linecap="round"
     stroke-linejoin="round">
    <path d="M3 3h18v4H3z"/>
    <path d="M5 7v14h14V7"/>
    <path d="M9 11h6"/>
    <path d="M9 15h6"/>
</svg>