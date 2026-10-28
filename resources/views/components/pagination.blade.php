@props(['paginator'])

@if($paginator->hasPages())
    <div class="flex items-center justify-between mt-4 text-sm">
        <div class="text-slate-500">
            Showing <span class="font-medium">{{ $paginator->firstItem() }}</span>
            to <span class="font-medium">{{ $paginator->lastItem() }}</span>
            of <span class="font-medium">{{ $paginator->total() }}</span> results
        </div>
        <div>
            {{ $paginator->links('pagination::tailwind') }}
        </div>
    </div>
@endif