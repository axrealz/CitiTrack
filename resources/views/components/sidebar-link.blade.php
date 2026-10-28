@props(['route', 'icon' => null, 'label' => ''])

@php
    $active = request()->routeIs($route)
        || request()->routeIs(str_replace('.index', '.*', $route));
@endphp

<a href="{{ route($route) }}"
   class="block px-3 py-2 rounded text-sm transition
          {{ $active ? 'bg-amber-500 text-slate-900 font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
    {{ $label }}
</a>