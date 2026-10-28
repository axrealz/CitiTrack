@props([
    'label'        => 'Confirm',
    'title'        => 'Are you sure?',
    'message'      => null,
    'subject'      => null,
    'confirmLabel' => 'Confirm',
    'cancelLabel'  => 'Cancel',
    'variant'      => 'danger',
    'initial'      => null,
])

@php
    $uid    = 'cd-' . \Illuminate\Support\Str::random(8);
    $danger = $variant === 'danger';
@endphp

<span class="contents" x-data="confirmAction({ initial: @js($initial) })">

    <button type="submit" aria-haspopup="dialog" {{ $attributes }}>{{ $label }}</button>

    <dialog x-ref="dlg"
            class="cn-dialog"
            aria-labelledby="{{ $uid }}-title"
            aria-describedby="{{ $uid }}-desc"
            @close="onClose()"
            @click="if ($event.target === $refs.dlg) cancel()">

        <div class="cn-dialog__body">
            <div class="flex gap-4">
                <div class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                            {{ $danger ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}"
                     aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m0 3.75h.008M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h2 id="{{ $uid }}-title" class="text-lg font-semibold text-slate-900">{{ $title }}</h2>

                    <div id="{{ $uid }}-desc" class="mt-2 space-y-3 text-sm text-slate-700">
                        @if($message)
                            <p>{{ $message }}</p>
                        @endif

                        @if($subject)
                            <p class="rounded-md bg-slate-100 px-3 py-2 font-medium text-slate-900 break-words">{{ $subject }}</p>
                        @endif

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        <div class="cn-dialog__footer">
            <button type="button" class="btn-secondary justify-center" autofocus @click="cancel()">
                {{ $cancelLabel }}
            </button>

            <button type="button"
                    class="{{ $danger ? 'btn-danger' : 'btn-primary' }} justify-center"
                    :disabled="busy"
                    @click="confirm()">
                <span x-show="!busy">{{ $confirmLabel }}</span>
                <span x-show="busy">Please wait…</span>
            </button>
        </div>
    </dialog>
</span>