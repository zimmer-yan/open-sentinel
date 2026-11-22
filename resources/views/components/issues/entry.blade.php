@props([
    'issue',
    'organization',
])

@php
    $latestEvent = $issue->events->first();
    $resolved = $issue->status === 'resolved';
@endphp

<a
    href="{{ route('organizations.issues.show', [$organization, $issue]) }}"
    class="block border-b border-gray-800 px-5 py-4 transition last:border-0 hover:bg-gray-800/50"
>
    <div class="flex flex-wrap items-start justify-between gap-4">

        <div class="flex min-w-0 flex-1 items-start gap-3">

            {{-- Status indicator --}}
            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full
                {{ $resolved ? 'bg-emerald-400' : 'bg-red-400' }}">
            </span>

            <div class="min-w-0 flex-1">

                {{-- Title --}}
                <h3 class="break-words text-sm font-medium text-gray-100">
                    {{ $issue->title }}
                </h3>

                {{-- Metadata --}}
                <p class="mt-1 text-xs text-gray-500">
                    <span class="text-violet-400">
                        {{ $issue->project->name }}
                    </span>

                    <span class="mx-1">·</span>

                    {{ $issue->type ?? 'Error' }}

                    <span class="mx-1">·</span>

                    {{ number_format($issue->events_count) }}
                    {{ \Illuminate\Support\Str::plural('event', $issue->events_count) }}
                </p>

                {{-- Latest event --}}
                @if ($latestEvent)

                    <p class="mt-2 line-clamp-2 break-words text-sm text-gray-400">
                        {{ $latestEvent->message ?: 'No event message available' }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Latest event:

                        {{ $latestEvent->occurred_at?->format('M j, Y H:i:s') ?? 'Unknown time' }}

                        @if ($latestEvent->environment)
                            <span class="mx-1">·</span>
                            {{ $latestEvent->environment }}
                        @endif

                        @if ($latestEvent->release)
                            <span class="mx-1">·</span>
                            {{ $latestEvent->release }}
                        @endif
                    </p>

                @else

                    <p class="mt-2 text-sm text-gray-500">
                        No events associated with this issue.
                    </p>

                @endif

            </div>
        </div>

        {{-- Right side --}}
        <div class="flex shrink-0 items-center gap-3">

            <span class="rounded-full px-2.5 py-1 text-xs
                {{ $resolved
                    ? 'bg-emerald-950 text-emerald-300'
                    : 'bg-red-950 text-red-300' }}">
                {{ ucfirst($issue->status ?? 'unresolved') }}
            </span>

            <span class="text-xs text-gray-500">
                {{ $issue->last_seen_at?->diffForHumans() ?? 'Unknown' }}
            </span>

            <span class="text-gray-500" aria-hidden="true">
                →
            </span>

        </div>

    </div>
</a>
