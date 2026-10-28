@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null])

<div>
    @if($label)
        <label for="{{ $name }}" class="label">
            {{ $label }}@if($required)<span class="text-red-600 ml-1">*</span>@endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'input']) }}
    >

    @if($hint)
        <p class="text-xs text-slate-600 mt-1">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>