
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-2">
        <span class="rounded-md bg-gray-800 px-2.5 py-1 text-xs font-semibold uppercase text-gray-300">
            {{ $event->level ?? 'error' }}
        </span>

        @if ($event->platform)
            <span class="rounded-md bg-gray-800 px-2.5 py-1 text-xs text-gray-300">
                {{ $event->platform }}
            </span>
        @endif

        @if ($event->environment)
            <span class="rounded-md bg-gray-800 px-2.5 py-1 text-xs text-gray-300">
                {{ $event->environment }}
            </span>
        @endif

        @if ($event->release)
            <span class="rounded-md bg-gray-800 px-2.5 py-1 text-xs text-gray-400">
                {{ $event->release }}
            </span>
        @endif
    </div>

    <div>
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
            Message
        </p>
        <pre class="mt-2 whitespace-pre-wrap break-words font-mono text-sm text-gray-200">{{ $event->message ?: 'No message available' }}</pre>
    </div>

    @if ($event->exception_type || $event->exception_value)
        <div class="rounded-lg border border-red-900/60 bg-red-950/20 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-red-300">
                Exception
            </p>

            @if ($event->exception_type)
                <p class="mt-2 break-words font-mono text-sm text-red-200">
                    {{ $event->exception_type }}
                </p>
            @endif

            @if ($event->exception_value)
                <pre class="mt-2 whitespace-pre-wrap break-words font-mono text-sm text-gray-300">{{ $event->exception_value }}</pre>
            @endif
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Occurred at
            </p>
            <p class="mt-2 text-sm text-gray-300">
                {{ $event->occurred_at?->format('M j, Y H:i:s T') ?? 'Unknown' }}
            </p>
        </div>

        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Received at
            </p>
            <p class="mt-2 text-sm text-gray-300">
                {{ $event->received_at?->format('M j, Y H:i:s T') ?? 'Unknown' }}
            </p>
        </div>

        @if ($event->runtime_name || $event->runtime_version)
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    Runtime
                </p>
                <p class="mt-2 text-sm text-gray-300">
                    {{ $event->runtime_name ?? 'Unknown' }}
                    @if ($event->runtime_version)
                        {{ $event->runtime_version }}
                    @endif
                </p>
            </div>
        @endif

        @if ($event->server_name)
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    Server
                </p>
                <p class="mt-2 break-words text-sm text-gray-300">
                    {{ $event->server_name }}
                </p>
            </div>
        @endif

        @if ($event->sdk_name || $event->sdk_version)
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                    SDK
                </p>
                <p class="mt-2 break-words text-sm text-gray-300">
                    {{ $event->sdk_name ?? 'Unknown' }}
                    @if ($event->sdk_version)
                        {{ $event->sdk_version }}
                    @endif
                </p>
            </div>
        @endif
    </div>

    @if ($event->trace_id || $event->span_id)
        <div class="rounded-lg border border-gray-800 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Distributed trace
            </p>

            @if ($event->trace_id)
                <div class="mt-3">
                    <p class="text-xs text-gray-500">Trace ID</p>
                    <p class="mt-1 break-all font-mono text-xs text-gray-300">
                        {{ $event->trace_id }}
                    </p>
                </div>
            @endif

            @if ($event->span_id)
                <div class="mt-3">
                    <p class="text-xs text-gray-500">Span ID</p>
                    <p class="mt-1 break-all font-mono text-xs text-gray-300">
                        {{ $event->span_id }}
                    </p>
                </div>
            @endif
        </div>
    @endif


    @if (is_array($event->stacktrace) && count($event->stacktrace))
        @php
            $frames = array_reverse($event->stacktrace);

            // Group consecutive frames by in_app status.
            $groups = [];

            foreach ($frames as $frame) {
                $inApp = (bool) ($frame['in_app'] ?? false);

                $lastIndex = count($groups) - 1;

                if ($lastIndex >= 0 && $groups[$lastIndex]['in_app'] === $inApp) {
                    $groups[$lastIndex]['frames'][] = $frame;
                } else {
                    $groups[] = [
                        'in_app' => $inApp,
                        'frames' => [$frame],
                    ];
                }
            }

            $frameNumber = 0;
        @endphp

        <details class="group rounded-xl border border-gray-800 bg-gray-900">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-xl px-4 py-3 transition hover:bg-gray-800/50">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-gray-500 transition-transform group-open:rotate-90"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7" />
                    </svg>

                    <span class="text-sm font-medium text-gray-200">Stack trace</span>

                    <span class="rounded-full bg-gray-800 px-2 py-0.5 text-xs text-gray-400">
                        {{ count($frames) }} frames
                    </span>
                </div>
            </summary>

            <div class="space-y-3 border-t border-gray-800 p-4">

                @foreach ($groups as $group)
                    @if ($group['in_app'])
                        {{-- Application frames --}}
                        @foreach ($group['frames'] as $frame)
                            @php
                                $frameNumber++;

                                $function = $frame['function']
                                    ?? $frame['raw_function']
                                    ?? '(unknown function)';

                                $filename = $frame['filename']
                                    ?? $frame['abs_path']
                                    ?? 'Unknown file';

                                $preContext = $frame['pre_context'] ?? [];
                                $contextLine = $frame['context_line'] ?? null;
                                $postContext = $frame['post_context'] ?? [];
                            @endphp

                            <div class="overflow-hidden rounded-lg border border-violet-500/40 bg-violet-950/10">

                                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-800 px-4 py-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-violet-500/20 text-xs font-semibold text-violet-300">
                                            {{ $frameNumber }}
                                        </span>

                                        <p class="break-all font-mono text-xs font-medium text-violet-200">
                                            {{ $function }}
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-full bg-violet-500/15 px-2.5 py-1 text-xs font-medium text-violet-300">
                                        In application
                                    </span>
                                </div>

                                <div class="border-b border-gray-800 px-4 py-3">
                                    <p class="break-all font-mono text-xs text-gray-400">
                                        {{ $filename }}@if (isset($frame['lineno'])):{{ $frame['lineno'] }}@endif
                                    </p>
                                </div>

                                @if (count($preContext) || $contextLine !== null || count($postContext))
                                    <div class="overflow-x-auto bg-gray-950 py-3">
                                        <div class="min-w-max font-mono text-xs leading-6">
                                            @foreach ($preContext as $line)
                                                <div class="flex whitespace-pre px-4 text-gray-500">
                                                    <span class="w-8 shrink-0"></span>
                                                    <span class="pl-3">{{ $line }}</span>
                                                </div>
                                            @endforeach

                                            @if ($contextLine !== null)
                                                <div class="flex whitespace-pre border-y border-red-500/20 bg-red-500/10 px-4 text-red-200">
                                                    <span class="w-8 shrink-0 text-red-400">▶</span>
                                                    <span class="pl-3 font-semibold">{{ $contextLine }}</span>
                                                </div>
                                            @endif

                                            @foreach ($postContext as $line)
                                                <div class="flex whitespace-pre px-4 text-gray-500">
                                                    <span class="w-8 shrink-0"></span>
                                                    <span class="pl-3">{{ $line }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                    @else
                        {{-- Consecutive external frames grouped together --}}
                        @php
                            $externalFrames = $group['frames'];
                            $externalCount = count($externalFrames);
                            $firstExternal = $externalFrames[0];
                            $lastExternal = $externalFrames[$externalCount - 1];
                        @endphp

                        <details class="overflow-hidden rounded-lg border border-gray-800 bg-gray-950/40">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 transition hover:bg-gray-800/50">
                                <div class="flex min-w-0 items-center gap-3">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-gray-500 transition-transform"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>

                                    <div>
                                        <p class="text-sm font-medium text-gray-300">
                                            {{ $externalCount }} external {{ $externalCount === 1 ? 'frame' : 'frames' }}
                                        </p>
                                        <p class="mt-1 truncate font-mono text-xs text-gray-500">
                                            {{ $firstExternal['function'] ?? $firstExternal['raw_function'] ?? '(unknown function)' }}
                                            @if ($externalCount > 1)
                                                &rarr; {{ $lastExternal['function'] ?? $lastExternal['raw_function'] ?? '(unknown function)' }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <span class="shrink-0 rounded-full bg-gray-800 px-2.5 py-1 text-xs text-gray-400">
                                    External
                                </span>
                            </summary>

                            <div class="space-y-2 border-t border-gray-800 p-3">
                                @foreach ($externalFrames as $frame)
                                    @php
                                        $frameNumber++;

                                        $function = $frame['function']
                                            ?? $frame['raw_function']
                                            ?? '(unknown function)';

                                        $filename = $frame['filename']
                                            ?? $frame['abs_path']
                                            ?? 'Unknown file';

                                        $preContext = $frame['pre_context'] ?? [];
                                        $contextLine = $frame['context_line'] ?? null;
                                        $postContext = $frame['post_context'] ?? [];
                                    @endphp

                                    <details class="overflow-hidden rounded-lg border border-gray-800">
                                        <summary class="flex cursor-pointer list-none items-center gap-3 px-3 py-2.5 hover:bg-gray-800/40">
                                            <span class="w-7 shrink-0 text-center text-xs text-gray-600">
                                                {{ $frameNumber }}
                                            </span>

                                            <span class="min-w-0 flex-1 truncate font-mono text-xs text-gray-400">
                                                {{ $function }}
                                            </span>

                                            <span class="shrink-0 text-xs text-gray-600">
                                                {{ $frame['lineno'] ?? '?' }}
                                            </span>
                                        </summary>

                                        <div class="border-t border-gray-800">
                                            <div class="px-3 py-2">
                                                <p class="break-all font-mono text-xs text-gray-500">
                                                    {{ $filename }}@if (isset($frame['lineno'])):{{ $frame['lineno'] }}@endif
                                                </p>
                                            </div>

                                            @if (count($preContext) || $contextLine !== null || count($postContext))
                                                <div class="overflow-x-auto border-t border-gray-800 bg-gray-950 py-3">
                                                    <div class="min-w-max font-mono text-xs leading-6">
                                                        @foreach ($preContext as $line)
                                                            <div class="flex whitespace-pre px-4 text-gray-500">
                                                                <span class="w-8 shrink-0"></span>
                                                                <span class="pl-3">{{ $line }}</span>
                                                            </div>
                                                        @endforeach

                                                        @if ($contextLine !== null)
                                                            <div class="flex whitespace-pre border-y border-red-500/20 bg-red-500/10 px-4 text-red-200">
                                                                <span class="w-8 shrink-0 text-red-400">▶</span>
                                                                <span class="pl-3 font-semibold">{{ $contextLine }}</span>
                                                            </div>
                                                        @endif

                                                        @foreach ($postContext as $line)
                                                            <div class="flex whitespace-pre px-4 text-gray-500">
                                                                <span class="w-8 shrink-0"></span>
                                                                <span class="pl-3">{{ $line }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </details>
                    @endif
                @endforeach

            </div>
        </details>
    @endif



    @if ($event->event_id)
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Event ID
            </p>
            <p class="mt-2 break-all font-mono text-xs text-gray-300">
                {{ $event->event_id }}
            </p>
        </div>
    @endif

    @if (is_array($event->contexts) && count($event->contexts))
        <details class="rounded-lg border border-gray-800">
            <summary class="cursor-pointer px-4 py-3 text-sm font-medium hover:bg-gray-800/50">
                Additional contexts
            </summary>
            <pre class="overflow-x-auto border-t border-gray-800 p-4 text-xs text-gray-300">{{ json_encode($event->contexts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        </details>
    @endif

    @if (is_array($event->payload) && count($event->payload))
        <details class="rounded-lg border border-gray-800">
            <summary class="cursor-pointer px-4 py-3 text-sm font-medium hover:bg-gray-800/50">
                View full redacted payload
            </summary>
            <pre class="overflow-x-auto border-t border-gray-800 p-4 text-xs text-gray-300">{{ json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        </details>
    @endif

    
    @if (is_array($event->request) && count($event->request))
        <details class="rounded-lg border border-gray-800">
            <summary class="cursor-pointer px-4 py-3 text-sm font-medium hover:bg-gray-800/50">
                HTTP request
            </summary>

            <div class="space-y-4 border-t border-gray-800 p-4">
                @if (!empty($event->request['method']))
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Method
                        </p>
                        <p class="mt-1 font-mono text-sm text-gray-200">
                            {{ $event->request['method'] }}
                        </p>
                    </div>
                @endif

                @if (!empty($event->request['url']))
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            URL
                        </p>
                        <p class="mt-1 break-all font-mono text-sm text-gray-200">
                            {{ $event->request['url'] }}
                        </p>
                    </div>
                @endif

                @if (!empty($event->request['headers']) && is_array($event->request['headers']))
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Headers
                        </p>
                        <pre class="mt-2 overflow-x-auto whitespace-pre-wrap break-words rounded bg-gray-950 p-3 text-xs text-gray-300">{{ json_encode($event->request['headers'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif

                @if (!empty($event->request['data']))
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Request body
                        </p>
                        <pre class="mt-2 overflow-x-auto whitespace-pre-wrap break-words rounded bg-gray-950 p-3 text-xs text-gray-300">{{ json_encode($event->request['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
            </div>
        </details>
    @endif

    @if (is_array($event->breadcrumbs) && count($event->breadcrumbs))
        <details class="rounded-lg border border-gray-800">
            <summary class="cursor-pointer px-4 py-3 text-sm font-medium hover:bg-gray-800/50">
                Breadcrumbs
                <span class="ml-1 text-gray-500">
                    ({{ count($event->breadcrumbs) }})
                </span>
            </summary>

            <div class="space-y-4 border-t border-gray-800 p-4">
                @foreach ($event->breadcrumbs as $breadcrumb)
                    @if (is_array($breadcrumb))
                        <div class="relative border-l-2 border-gray-700 pl-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded bg-gray-800 px-2 py-1 text-xs text-gray-300">
                                    {{ $breadcrumb['category'] ?? $breadcrumb['type'] ?? 'event' }}
                                </span>

                                @if (!empty($breadcrumb['level']))
                                    <span class="text-xs text-gray-500">
                                        {{ $breadcrumb['level'] }}
                                    </span>
                                @endif
                            </div>

                            <p class="mt-2 whitespace-pre-wrap break-words text-sm text-gray-200">{{ $breadcrumb['message'] ?? 'No message' }}</p>

                            @if (isset($breadcrumb['timestamp']) && is_numeric($breadcrumb['timestamp']))
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ \Illuminate\Support\Carbon::createFromTimestamp((float) $breadcrumb['timestamp'])->toDateTimeString() }}
                                </p>
                            @endif

                            @if (!empty($breadcrumb['data']) && is_array($breadcrumb['data']))
                                <pre class="mt-2 overflow-x-auto whitespace-pre-wrap break-words rounded bg-gray-950 p-3 text-xs text-gray-400">{{ json_encode($breadcrumb['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </details>
    @endif


</div>
