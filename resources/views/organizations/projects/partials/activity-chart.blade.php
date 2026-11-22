
@php
    $counts = $project->activity_counts ?? array_fill(0, 24, 0);
    $max = max(1, ...$counts);

    $width = 240;
    $height = 52;
    $padding = 4;
    $plotHeight = 38;
    $plotWidth = $width - ($padding * 2);

    $points = collect($counts)->map(function ($count, $index) use (
        $counts,
        $max,
        $padding,
        $plotWidth,
        $plotHeight,
        $height,
    ) {
        $x = $padding + ($index / max(1, count($counts) - 1)) * $plotWidth;
        $y = $height - 6 - (($count / $max) * $plotHeight);

        return [
            'x' => round($x, 2),
            'y' => round($y, 2),
            'count' => $count,
        ];
    });

    $pointsAttribute = $points
        ->map(fn ($point) => "{$point['x']},{$point['y']}")
        ->implode(' ');
@endphp

<div class="mt-5 border-t border-gray-800 pt-4">
    <div class="mb-3 flex items-center justify-between gap-3">
        <p class="text-xs font-medium text-gray-400">
            Event activity
        </p>

        <p class="text-xs text-gray-500">
            Last 24 hours
        </p>
    </div>

    <svg
        viewBox="0 0 {{ $width }} {{ $height }}"
        class="h-14 w-full overflow-visible"
        role="img"
        aria-label="Hourly event activity over the last 24 hours"
        preserveAspectRatio="none"
    >
        <line
            x1="{{ $padding }}"
            y1="{{ $height - 6 }}"
            x2="{{ $width - $padding }}"
            y2="{{ $height - 6 }}"
            stroke="currentColor"
            stroke-opacity="0.2"
            stroke-width="1"
        />

        <polyline
            points="{{ $pointsAttribute }}"
            fill="none"
            stroke="#a78bfa"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            vector-effect="non-scaling-stroke"
        />

        @foreach ($points as $index => $point)
            <circle
                cx="{{ $point['x'] }}"
                cy="{{ $point['y'] }}"
                r="2"
                fill="#a78bfa"
            >
                <title>
                    {{ now()->startOfHour()->subHours(23 - $index)->format('M j, H:i') }}:
                    {{ $point['count'] }} events
                </title>
            </circle>
        @endforeach
    </svg>

    <div class="mt-2 flex items-center justify-between text-xs">
        <span class="text-gray-500">
            {{ now()->startOfHour()->subHours(23)->format('H:i') }}
        </span>

        <span class="font-medium text-violet-300">
            {{ number_format($project->events_last_24_hours ?? 0) }} events
        </span>

        <span class="text-gray-500">
            Now
        </span>
    </div>
</div>
