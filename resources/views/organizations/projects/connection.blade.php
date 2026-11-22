@extends('open-sentinel::layouts.app')

@section('title', 'Connect project · ' . $project->name)

@section('content')
    <div class="mb-8">
        <a href="{{ route('organizations.overview', [$organization, $project]) }}"
           class="text-sm text-gray-400 hover:text-white">
            ← {{ $project->name }}
        </a>

        <h1 class="mt-5 text-3xl font-semibold tracking-tight">
            Connect your application
        </h1>

        <p class="mt-2 max-w-2xl text-gray-400">
            Configure your application to send exceptions and events to
            this project.
        </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <section class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-gray-800 bg-gray-900 p-6">
                <h2 class="text-lg font-semibold">1. Your project DSN</h2>

                <p class="mt-2 text-sm leading-6 text-gray-400">
                    Use this public DSN to identify the project when sending
                    events. It is not a secret credential.
                </p>

                @if ($activeKey)
                    @php
                        $dsn = request()->getScheme()
                            . '://'
                            . $activeKey->public_key
                            . '@'
                            . request()->getHttpHost()
                            . '/'
                            . $project->id;
                    @endphp

                    <label for="dsn" class="mb-2 mt-5 block text-sm font-medium">
                        DSN
                    </label>

                    <div class="flex gap-2">
                        <input id="dsn" readonly value="{{ $dsn }}"
                               class="min-w-0 flex-1 rounded-lg border border-gray-700 bg-gray-950 px-3 py-3 font-mono text-xs text-gray-200">
                        <button type="button"
                                onclick="navigator.clipboard.writeText(document.getElementById('dsn').value)"
                                class="shrink-0 rounded-lg border border-gray-700 px-4 py-2 text-sm hover:bg-gray-800">
                            Copy
                        </button>
                    </div>
                @else
                    <div class="mt-5 rounded-lg border border-amber-800 bg-amber-950/40 p-4">
                        <p class="text-sm text-amber-200">
                            No active key. Generate a key to connect your application.
                        </p>
                    </div>
                @endif

                @if (! $activeKey)
                    <form method="POST"
                          action="{{ route('organizations.projects.keys.rotate', [$organization, $project]) }}"
                          class="mt-4">
                        @csrf
                        <button class="rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-medium hover:bg-violet-500">
                            Generate project key
                        </button>
                    </form>
                @endif
            </div>

            <div class="rounded-xl border border-gray-800 bg-gray-900 p-6">
                <h2 class="text-lg font-semibold">2. Connect a Laravel application</h2>

                <p class="mt-2 text-sm text-gray-400">
                    Your server-side SDK or custom client must send events
                    to the ingestion endpoint. The following environment
                    variable is an example configuration.
                </p>

                <pre class="mt-4 overflow-x-auto rounded-lg border border-gray-800 bg-gray-950 p-4 text-sm text-gray-300"><code>TRACE_DSN={{ $activeKey ? $dsn : 'Generate a key first' }}</code></pre>

                <p class="mt-4 text-sm leading-6 text-gray-400">
                    In your Laravel application, read the value with
                    <code class="text-gray-200">env('TRACE_DSN')</code>
                    through a configuration file. You'll need an SDK or
                    HTTP client that understands your envelope format.
                </p>
            </div>

            <div class="rounded-xl border border-gray-800 bg-gray-900 p-6">
                <h2 class="text-lg font-semibold">3. Send an event</h2>

                <p class="mt-2 text-sm leading-6 text-gray-400">
                    Your server should POST a Sentry-compatible envelope to
                    the DSN endpoint. An envelope starts with a JSON header,
                    followed by item headers and their payloads.
                </p>

                <div class="mt-4 rounded-lg border border-gray-800 bg-gray-950 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Expected request
                    </p>
                    <p class="mt-2 font-mono text-sm text-gray-200">POST /api/{{ $project->id }}/envelope/</p>
                    <p class="mt-2 text-sm text-gray-400">
                        Content-Type: application/x-sentry-envelope
                    </p>
                </div>

                <p class="mt-4 text-sm text-amber-300">
                    The endpoint URL above is a proposed route. It will not
                    accept events until you implement and register the
                    ingestion controller.
                </p>
            </div>
        </section>

        <aside class="h-fit rounded-xl border border-gray-800 bg-gray-900 p-6">
            <h2 class="text-lg font-semibold">Project keys</h2>
            <p class="mt-2 text-sm leading-6 text-gray-400">
                Rotate keys when you need to replace a DSN or invalidate
                an existing client configuration.
            </p>

            @if ($activeKey)
                <form method="POST"
                      action="{{ route('organizations.projects.keys.rotate', [$organization, $project]) }}"
                      class="mt-5"
                      onsubmit="return confirm('Rotate the project key? Existing clients using the old key will stop working.');">
                    @csrf
                    <button class="w-full rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-medium hover:bg-violet-500">
                        Rotate project key
                    </button>
                </form>
            @endif

            <div class="mt-6 space-y-4">
                @forelse ($keys as $key)
                    <div class="border-t border-gray-800 pt-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-medium">
                                {{ $key->revoked_at ? 'Revoked key' : 'Active key' }}
                            </span>

                            <span class="rounded-full px-2 py-1 text-xs
                                {{ $key->revoked_at
                                    ? 'bg-gray-800 text-gray-400'
                                    : 'bg-emerald-950 text-emerald-300' }}">
                                {{ $key->revoked_at ? 'Revoked' : 'Active' }}
                            </span>
                        </div>

                        <p class="mt-2 break-all font-mono text-xs text-gray-500">
                            {{ $key->public_key }}
                        </p>

                        @if ($key->last_used_at)
                            <p class="mt-2 text-xs text-gray-500">
                                Last used {{ $key->last_used_at->diffForHumans() }}
                            </p>
                        @endif

                        @if (! $key->revoked_at)
                            <form method="POST"
                                  action="{{ route('organizations.projects.keys.revoke', [$organization, $project, $key]) }}"
                                  class="mt-3"
                                  onsubmit="return confirm('Revoke this key? Clients using it will stop working.');">
                                @csrf
                                @method('DELETE')

                                <button class="text-sm text-red-400 hover:text-red-300">
                                    Revoke key
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="border-t border-gray-800 pt-4 text-sm text-gray-500">
                        No keys have been created.
                    </p>
                @endforelse
            </div>
        </aside>
    </div>
@endsection