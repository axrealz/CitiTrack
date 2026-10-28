@props([
    'action',
    'label'   => 'Delete',
    'confirm' => null,
    'entity'  => null,
    'subject' => null,
])

<x-confirm-dialog
    :action="$action"
    method="DELETE"
    :buttonLabel="$label"
    :confirmText="$confirm"
    :entity="$entity"
    :subject="$subject"
    buttonClass="text-red-700 hover:text-red-900 text-sm font-medium"
/>