
@extends('open-sentinel::layouts.project')

@section('title', $issue->title)

@section('project-content')
    <div class="mb-8">

        <div class="mt-5 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="h-3 w-3 shrink-0 rounded-full
                        {{ $issue->status === 'resolved'
                            ? 'bg-emerald-400'
                            : 'bg-red-400' }}">
                    </span>

                    <h1 class="break-words text-3xl font-semibold tracking-tight">
                        {{ $issue->title }}
                    </h1>

                    <span class="rounded-full px-2.5 py-1 text-xs
                        {{ $issue->status === 'resolved'
                            ? 'bg-emerald-950 text-emerald-300'
                            : 'bg-red-950 text-red-300' }}">
                        {{ ucfirst($issue->status ?? 'unresolved') }}
                    </span>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2 text-sm text-gray-400">
                    <span>{{ ucfirst($issue->type ?? 'error') }}</span>
                    <span>·</span>
                    <span>{{ $issue->project->platform ?? 'Unknown platform' }}</span>
                    <span>·</span>
                    <span>
                        {{ number_format($issue->event_count) }}
                        {{ \Illuminate\Support\Str::plural('event', $issue->event_count) }}
                    </span>
                </div>

                <div class="mt-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Fingerprint
                    </p>
                    <p class="mt-1 break-all font-mono text-xs text-gray-400">
                        {{ $issue->fingerprint_hash }}
                    </p>
                </div>
            </div>
        </div>

        @if ($issue->status !== 'resolved')
            <form
                action="{{ route('organizations.issues.' . ($issue->status === 'resolved' ? 'reopen' : 'resolve'), [$organization, $issue]) }}"
                method="POST"
            >
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition
                        {{ $issue->status === 'resolved'
                            ? 'border border-gray-700 text-gray-300 hover:bg-gray-800'
                            : 'bg-emerald-600 text-white hover:bg-emerald-500' }}"
                >
                    {{ $issue->status === 'resolved' ? 'Reopen issue' : 'Resolve issue' }}
                </button>
            </form>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
            <p class="text-sm text-gray-400">Total events</p>
            <p class="mt-3 text-3xl font-semibold">
                {{ number_format($issue->event_count) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
            <p class="text-sm text-gray-400">First seen</p>
            <p class="mt-3 text-lg font-medium">
                {{ $issue->first_seen_at?->format('M j, Y H:i:s') ?? 'Unknown' }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
                {{ $issue->first_seen_at?->diffForHumans() ?? '' }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
            <p class="text-sm text-gray-400">Last seen</p>
            <p class="mt-3 text-lg font-medium">
                {{ $issue->last_seen_at?->format('M j, Y H:i:s') ?? 'Unknown' }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
                {{ $issue->last_seen_at?->diffForHumans() ?? '' }}
            </p>
        </div>
    </div>

    <section class="mt-8 overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
        <div class="border-b border-gray-800 px-5 py-4">
            <h2 class="font-semibold">Latest event</h2>
            <p class="mt-1 text-sm text-gray-500">
                Most recent occurrence of this issue
            </p>
        </div>

        @if ($latestEvent)
            <div class="p-5">
                @include('open-sentinel::organizations.issues.partials.event-details', [
                    'event' => $latestEvent,
                ])
            </div>
        @else
            <div class="p-8 text-center text-sm text-gray-400">
                No events have been recorded for this issue yet.
            </div>
        @endif
    </section>

    <section class="mt-8 overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
        <div class="border-b border-gray-800 px-5 py-4">
            <h2 class="font-semibold">Event history</h2>
            <p class="mt-1 text-sm text-gray-500">
                Browse individual occurrences and inspect their diagnostic details.
            </p>
        </div>

        @forelse ($events as $event)
            <details class="border-b border-gray-800 last:border-0">
                <summary class="cursor-pointer list-none px-5 py-4 hover:bg-gray-800/50">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <span class="mt-0.5 shrink-0 rounded bg-gray-800 px-2 py-1 text-xs uppercase text-gray-300">
                                {{ $event->level ?? 'error' }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="break-words text-sm text-gray-200">
                                    {{ \Illuminate\Support\Str::limit(
                                        $event->message
                                            ?: $event->exception_value
                                            ?: 'No message available',
                                        180
                                    ) }}
                                </p>

                                @if ($event->exception_type)
                                    <p class="mt-1 break-words font-mono text-xs text-red-300">
                                        {{ $event->exception_type }}
                                    </p>
                                @endif

                                <p class="mt-2 text-xs text-gray-500">
                                    {{ $event->occurred_at?->format('M j, Y H:i:s T') ?? 'Unknown occurrence time' }}
                                </p>

                                <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-500">
                                    @if ($event->environment)
                                        <span class="rounded bg-gray-800 px-2 py-1">
                                            {{ $event->environment }}
                                        </span>
                                    @endif

                                    @if ($event->release)
                                        <span class="rounded bg-gray-800 px-2 py-1">
                                            {{ $event->release }}
                                        </span>
                                    @endif

                                    @if ($event->platform)
                                        <span class="rounded bg-gray-800 px-2 py-1">
                                            {{ $event->platform }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <span class="shrink-0 text-xs text-gray-500">
                            {{ $event->received_at?->diffForHumans() ?? 'Unknown' }}
                        </span>
                    </div>
                </summary>

                <div class="border-t border-gray-800 bg-gray-950/40 p-5">
                    @include('open-sentinel::organizations.issues.partials.event-details', [
                        'event' => $event,
                    ])
                </div>
            </details>
        @empty
            <div class="px-5 py-12 text-center">
                <h3 class="font-medium">No events found</h3>
                <p class="mt-2 text-sm text-gray-400">
                    Events associated with this issue will appear here.
                </p>
            </div>
        @endforelse

        @if ($events->hasPages())
            <div class="border-t border-gray-800 px-5 py-4">
                {{ $events->links() }}
            </div>
        @endif
    </section>
@endsection
