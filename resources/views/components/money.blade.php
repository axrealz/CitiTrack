@props(['amount' => 0, 'class' => ''])

<span {{ $attributes->merge(['class' => "font-mono tabular-nums {$class}"]) }}>
    ₱{{ number_format((float) $amount, 2) }}
</span>