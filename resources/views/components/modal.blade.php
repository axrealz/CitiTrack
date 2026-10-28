@props(['name', 'title' => '', 'maxWidth' => 'md'])

@php
    $maxWidths = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl'=> 'sm:max-w-2xl',
    ];
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal-{{ $name }}.window="open = true"
    x-on:close-modal-{{ $name }}.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display:none;"
>
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-50" x-on:click="open = false"></div>

        <div class="relative bg-white rounded-lg shadow-xl w-full {{ $maxWidths[$maxWidth] ?? $maxWidths['md'] }}">
            @if($title)
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">{{ $title }}</h3>
                    <button type="button" x-on:click="open = false" arial-label="Close"
                            class="text-slate-500 hover:text-slate-800">
                        &times;
                    </button>
                </div>
            @endif

            <div class="px-6 py-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>