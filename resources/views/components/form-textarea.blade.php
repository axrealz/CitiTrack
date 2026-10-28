@props(['name', 'label' => null, 'value' => null, 'rows' => 3, 'required' => false])

<div>
    @if($label)
        <label for="{{ $name }}" class="label">
            {{ $label }}@if($required)<span class="text-red-600 ml-1">*</span>@endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'input']) }}
    >{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>