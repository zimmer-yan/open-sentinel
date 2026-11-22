@extends('open-sentinel::layouts.app')

@section('title', 'Event ' . $event->event_id)

@section('content')
    <p>
        <a href="{{ route('projects.organizations.issues.show', [
            'project' => $project,
            'issue' => $event->issue,
        ]) }}">
            Back to issue
        </a>
    </p>

    <h1>{{ $event->message ?? 'Event details' }}</h1>

    <dl>
        <dt>Event ID</dt>
        <dd>{{ $event->event_id }}</dd>

        <dt>Level</dt>
        <dd>{{ $event->level ?? 'unknown' }}</dd>

        <dt>Environment</dt>
        <dd>{{ $event->environment ?? 'unknown' }}</dd>

        <dt>Release</dt>
        <dd>{{ $event->release ?? 'unknown' }}</dd>

        <dt>Occurred at</dt>
        <dd>{{ $event->occurred_at->toDateTimeString() }}</dd>
    </dl>

    <h2>Exception details</h2>

    @php
        $exceptions = data_get($event->payload, 'exception.values', []);
    @endphp

    @forelse ($exceptions as $exception)
        <section>
            <h3>{{ $exception['type'] ?? 'Exception' }}</h3>
            <p>{{ $exception['value'] ?? '' }}</p>

            @php
                $frames = data_get($exception, 'stacktrace.frames', []);
            @endphp

            @if (count($frames))
                <h4>Stack trace</h4>

                <ol>
                    @foreach (array_reverse($frames) as $frame)
                        <li>
                            <code>
                                {{ $frame['filename'] ?? '[unknown file]' }}:
                                {{ $frame['lineno'] ?? '?' }}
                                — {{ $frame['function'] ?? '[unknown function]' }}
                            </code>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    @empty
        <p>No exception stack trace was included in this event.</p>
    @endforelse

    <details>
        <summary>Raw event payload</summary>
        <pre>{{ json_encode(
            $event->payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ) }}</pre>
    </details>
@endsection