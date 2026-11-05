{{--
    "← Back" button, shown once at the top of the main content area.
    It is placed in the three role layouts (admin / cashier / stocker), so every
    page that uses them gets exactly one. Dashboards don't show it.

    How it works (see citiTrackBack() in resources/js/app.js):
      - If the user came from another CitiTrack page, it uses the browser history.
      - Otherwise (bookmark, new tab, after saving a form...) it follows the link
        below, which always points inside the same section of CitiTrack:
          create / edit / show page  -> that section's list page
          report pages               -> Reports
          anything else              -> the user's own dashboard
--}}
@props(['fallback' => null, 'label' => 'Back'])

@php
    $routeName = request()->route()?->getName() ?? '';

    // Nowhere sensible to go "back" to from a dashboard
    $hide = $routeName === 'dashboard' || str_ends_with($routeName, '.dashboard');

    if (! $hide && ! $fallback) {
        $home = match (auth()->user()?->role) {
            'admin'   => 'admin.dashboard',
            'cashier' => 'cashier.dashboard',
            'stocker' => 'stocker.dashboard',
            default   => 'dashboard',
        };

        $candidates = [];

        if (preg_match('/^(.+)\.(create|edit|show)$/', $routeName, $m)) {
            $candidates = [$m[1] . '.index', $m[1]];
        } elseif (str_starts_with($routeName, 'admin.reports.') && $routeName !== 'admin.reports.index') {
            $candidates = ['admin.reports.index'];
        }

        $target = $home;
        foreach ($candidates as $candidate) {
            if (\Illuminate\Support\Facades\Route::has($candidate)) {
                $target = $candidate;
                break;
            }
        }

        try {
            $fallback = route($target);
        } catch (\Throwable $e) {
            $fallback = route($home);   // a route that needs parameters: use the dashboard
        }
    }
@endphp

@unless($hide)
    <div class="mb-4 no-print">
        <a href="{{ $fallback }}"
           onclick="return citiTrackBack(event, this)"
           class="group inline-flex items-center gap-2 px-3 py-1.5 rounded-md border border-slate-400 bg-white
                  text-sm font-medium text-slate-700 shadow-sm transition
                  hover:bg-slate-100 hover:text-slate-900 hover:border-slate-500
                  focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-600 focus-visible:ring-offset-2">
            <span aria-hidden="true" class="transition-transform group-hover:-translate-x-0.5">←</span>
            <span>{{ $label }}</span>
        </a>
    </div>
@endunless