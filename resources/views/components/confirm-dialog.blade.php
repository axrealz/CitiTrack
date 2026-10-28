@props([
    'action',
    'method'       => 'POST',
    'message'      => null,
    'confirmText'  => null,
    'buttonLabel'  => 'Delete',
    'buttonClass'  => 'text-red-600 hover:text-red-800',
    'title'        => null,
    'subject'      => null,
    'entity'       => null,
    'confirmLabel' => null,
    'cancelLabel'  => 'Cancel',
    'variant'      => 'danger',
])

@php
    $isDelete = strtoupper($method) === 'DELETE';
    $dialogTitle = $title ?? ($isDelete ? 'Delete ' . ($entity ?? 'this item') . '?' : 'Are you sure?');
    $dialogText  = $message ?? $confirmText
                   ?? ($isDelete ? "You won't be able to undo this from the app." : 'Please confirm to continue.');
    $dialogConfirm = $confirmLabel ?? ($isDelete ? 'Confirm Delete' : 'Confirm');
@endphp

<form method="POST" action="{{ $action }}" class="inline">
    @csrf
    @if(! in_array(strtoupper($method), ['GET', 'POST']))
        @method($method)
    @endif

    <x-confirm-submit
        :label="$buttonLabel"
        :title="$dialogTitle"
        :message="$dialogText"
        :subject="$subject"
        :confirmLabel="$dialogConfirm"
        :cancelLabel="$cancelLabel"
        :variant="$variant"
        {{ $attributes->merge(['class' => $buttonClass]) }}
    />
</form>