@props(['title' => '', 'subtitle' => null, 'actions' => null])

<div class="card">
    @if($title || $actions)
        <div class="card-header flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-800">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if($actions)
                <div>{{ $actions }}</div>
            @endif
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>