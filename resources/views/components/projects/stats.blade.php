@props(['projects'])

@php
    $stats = [
        ['label' => 'Issues', 'value' => $projects->sum('issues_count'), 'hint' => 'Grouped exceptions'],
        ['label' => 'Events', 'value' => $projects->sum('events_count'), 'hint' => 'Captured exceptions'],
        ['label' => 'Log entries', 'value' => $projects->sum('log_entries_count'), 'hint' => 'Structured logs'],
    ];
@endphp

<div class="grid gap-4 sm:grid-cols-3">
    @foreach ($stats as $stat)
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
            <p class="text-sm text-gray-400">{{ $stat['label'] }}</p>
            <p class="mt-3 text-3xl font-semibold">
                {{ number_format($stat['value']) }}
            </p>
            <p class="mt-2 text-xs text-gray-500">{{ $stat['hint'] }}</p>
        </div>
    @endforeach
</div>
