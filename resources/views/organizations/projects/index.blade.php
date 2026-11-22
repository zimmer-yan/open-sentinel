@extends('open-sentinel::layouts.app')

@section('title', 'Projects · ' . $organization->name)

@section('content')
    <div class="mb-8">
        <a href="{{ route('organizations.index') }}"
           class="text-sm text-gray-400 hover:text-white">
            ← All organizations
        </a>

        <div class="mt-5 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-violet-400">
                    {{ $organization->name }}
                </p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight">Projects</h1>
                <p class="mt-2 text-gray-400">
                    Select a project to inspect its errors and events.
                </p>
            </div>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <section class="space-y-3 lg:col-span-2">
           @forelse ($projects as $project)
            <div class="group rounded-xl border border-gray-800 bg-gray-900 p-5 transition hover:border-violet-600">

                {{-- Project details link --}}
                <a href="{{ route('organizations.projects.index', [
                        'organization' => $organization,
                    ]) }}"
                class="block">

                    <div class="flex items-center justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gray-800">
                                <span class="text-lg">⌘</span>
                            </div>

                            <div class="min-w-0">
                                <h2 class="truncate font-semibold group-hover:text-violet-300">
                                    {{ $project->name }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ ucfirst($project->platform) }}
                                    · {{ $project->slug }}
                                </p>
                            </div>
                        </div>

                        <span class="rounded-full bg-emerald-950 px-2.5 py-1 text-xs text-emerald-300">
                            {{ $project->status ?? 'active' }}
                        </span>
                    </div>

                    <div class="mt-5 flex gap-6 border-t border-gray-800 pt-4 text-sm">
                        <p class="text-gray-400">
                            <span class="font-medium text-gray-200">
                                {{ $project->issues_count }}
                            </span>
                            issues
                        </p>

                        <p class="text-gray-400">
                            <span class="font-medium text-gray-200">
                                {{ $project->events_count }}
                            </span>
                            events
                        </p>
                    </div>

                    @include('open-sentinel::organizations.projects.partials.activity-chart', [
                        'project' => $project,
                    ])
                </a>

                {{-- Configure SDK link --}}
                <div class="mt-5 flex justify-end border-t border-gray-800 pt-4">
                    <a href="{{ route('organizations.projects.connection', [
                            'organization' => $organization,
                            'project' => $project,
                        ]) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-violet-500">
                        <span>⚡</span>
                        Configure SDK
                        <span>→</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-gray-700 p-10 text-center">
                <h2 class="font-semibold">No projects yet</h2>
                <p class="mt-2 text-sm text-gray-400">
                    Create a project for the application you want to monitor.
                </p>
            </div>
        @endforelse
        </section>

        <aside class="h-fit rounded-xl border border-gray-800 bg-gray-900 p-6">
            <h2 class="text-lg font-semibold">Create a project</h2>
            <p class="mt-2 text-sm text-gray-400">
                Each project gets its own events, issues, and ingestion keys.
            </p>

            <form method="POST"
                  action="{{ route('organizations.projects.store', $organization) }}"
                  class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="name" class="mb-2 block text-sm font-medium">
                        Project name
                    </label>
                    <input id="name" name="name" type="text"
                           value="{{ old('name') }}" required maxlength="100"
                           placeholder="My Laravel API"
                           class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 outline-none focus:border-violet-500">
                    @error('name')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="platform" class="mb-2 block text-sm font-medium">
                        Platform
                    </label>
                    <select id="platform" name="platform" required
                            class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 outline-none focus:border-violet-500">
                        @foreach ([
                            'laravel' => 'Laravel',
                            'php' => 'PHP',
                            'javascript' => 'JavaScript',
                            'python' => 'Python',
                            'node' => 'Node.js',
                            'other' => 'Other',
                        ] as $value => $label)
                            <option value="{{ $value }}"
                                @selected(old('platform', 'laravel') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('platform')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-violet-600 px-4 py-2.5 font-medium hover:bg-violet-500">
                    Create project
                </button>
            </form>
        </aside>
    </div>
@endsection
