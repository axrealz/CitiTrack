@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-500 text-gray-900 focus:border-indigo-600 focus:ring-indigo-600 disabled:bg-gray-100 disabled:text-gray-600 disabled:cursor-not-allowed rounded-md shadow-sm']) }}>
