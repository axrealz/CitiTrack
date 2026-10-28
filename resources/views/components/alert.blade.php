@props(['type' => 'info', 'message' => null])

@if($message)
    <div class="mb-4 px-4 py-3 rounded text-sm border
                {{ $type === 'success' ? 'bg-green-50 text-green-800 border-green-200' : '' }}
                {{ $type === 'error'   ? 'bg-red-50 text-red-800 border-red-200' : '' }}
                {{ $type === 'info'    ? 'bg-blue-50 text-blue-800 border-blue-200' : '' }}
                {{ $type === 'warning' ? 'bg-amber-50 text-amber-800 border-amber-200' : '' }}">
        {{ $message }}
    </div>
@endif