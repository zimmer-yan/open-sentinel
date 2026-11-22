@props(['log'])

@php
    $levelStyles = [
        'debug' => 'bg-gray-800 text-gray-300',
        'info' => 'bg-blue-950 text-blue-300',
        'notice' => 'bg-cyan-950 text-cyan-300',
        'warning' => 'bg-amber-950 text-amber-300',
        'error' => 'bg-red-950 text-red-300',
        'critical' => 'bg-red-950 text-red-300',
        'alert' => 'bg-red-950 text-red-300',
        'emergency' => 'bg-red-950 text-red-300',
    ];

    $level = strtolower($log->level ?? 'info');
@endphp

<details class="border-b border-gray-800 last:border-0">

    {{-- Summary --}}
    <summary class="cursor-pointer list-none px-5 py-4 hover:bg-gray-800/50">

        <div class="flex flex-wrap items-start justify-between gap-4">

            <div class="flex min-w-0 flex-1 items-start gap-3">

                {{-- Level badge --}}
                <span class="mt-0.5 shrink-0 rounded px-2 py-1 text-xs font-medium uppercase
                    {{ $levelStyles[$level] ?? 'bg-gray-800 text-gray-300' }}">
                    {{ $log->level ?? 'info' }}
                </span>

                <div class="min-w-0 flex-1">

                    {{-- Message --}}
                    <p class="break-words text-sm text-gray-200">
                        <span class="text-violet-400">
                            [{{ $log->project->name }}]
                        </span>

                        {{ $log->message ?: 'No message available' }}
                    </p>

                    {{-- Metadata --}}
                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">

                        <span>
                            {{ $log->occurred_at?->format('M j, Y H:i:s T') ?? 'Unknown time' }}
                        </span>

                        @if ($log->environment)
                            <span>{{ $log->environment }}</span>
                        @endif

                        @if ($log->release)
                            <span>{{ $log->release }}</span>
                        @endif

                        @if ($log->logger)
                            <span>{{ $log->logger }}</span>
                        @endif

                    </div>
                </div>
            </div>

            {{-- Received time --}}
            <span class="shrink-0 text-xs text-gray-500">
                {{ $log->received_at?->diffForHumans() ?? '' }}
            </span>

        </div>
    </summary>

    {{-- Expanded details --}}
    <div class="space-y-5 border-t border-gray-800 bg-gray-950/40 p-5">

        {{-- Full message --}}
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Full message
            </p>

            <pre class="mt-2 whitespace-pre-wrap break-words font-mono text-sm text-gray-300">{{ $log->message ?: 'No message available' }}</pre>
        </div>

        {{-- Timestamps --}}
        <div class="grid gap-4 sm:grid-cols-2">

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    Occurred at
                </p>

                <p class="mt-1 text-sm text-gray-300">
                    {{ $log->occurred_at?->format('M j, Y H:i:s T') ?? 'Unknown' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    Received at
                </p>

                <p class="mt-1 text-sm text-gray-300">
                    {{ $log->received_at?->format('M j, Y H:i:s T') ?? 'Unknown' }}
                </p>
            </div>

        </div>

        {{-- Batch ID --}}
        @if ($log->batch_id)
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    Batch ID
                </p>

                <p class="mt-1 break-all font-mono text-xs text-gray-400">
                    {{ $log->batch_id }}
                </p>
            </div>
        @endif

        {{-- Attributes --}}
        @if (is_array($log->attributes) && count($log->attributes))
            <x-open-sentinel::logs.attributes :log-attributes="$log->attributes" />
        @endif

    </div>

</details>
