@props(['name', 'label' => null, 'options' => [], 'value' => null, 'required' => false, 'placeholder' => '-- Select --'])

<div>
    @if($label)
        <label for="{{ $name }}" class="label">
            {{ $label }}@if($required)<span class="text-red-600 ml-1">*</span>@endif
        </label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'input']) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected(old($name, $value) == $optValue)>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>

    @error($name)
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>