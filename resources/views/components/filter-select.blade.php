@props(['name', 'options' => [], 'placeholder' => 'All', 'selected' => null])

<select name="{{ $name }}" class="input">
    <option value="">{{ $placeholder }}</option>
    @foreach($options as $value => $label)
        <option value="{{ $value }}" @selected(($selected ?? request($name)) == $value)>
            {{ $label }}
        </option>
    @endforeach
</select>