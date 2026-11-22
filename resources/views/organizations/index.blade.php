@extends('open-sentinel::layouts.app')

@section('title', 'Organizations')

@section('content')
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm text-violet-400">Workspace</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">
                Your organizations
            </h1>
            <p class="mt-2 text-gray-400">
                Select a workspace or create a new one.
            </p>
        </div>
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-3">
        <section class="space-y-3 lg:col-span-2">
            @forelse ($organizations as $organization)
                <a href="{{ route('organizations.projects.index', $organization) }}"
                   class="group flex items-center justify-between gap-4 rounded-xl border border-gray-800 bg-gray-900 p-5 transition hover:border-violet-600">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-950 text-lg font-semibold text-violet-300">
                            {{ strtoupper(substr($organization->name, 0, 1)) }}
                        </div>

                        <div class="min-w-0">
                            <h2 class="truncate font-semibold group-hover:text-violet-300">
                                {{ $organization->name }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $organization->projects_count }}
                                {{ Str::plural('project', $organization->projects_count) }}
                            </p>
                        </div>
                    </div>

                    <span class="text-gray-500 group-hover:text-violet-300">→</span>
                </a>
            @empty
                <div class="rounded-xl border border-dashed border-gray-700 p-10 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gray-900">
                        <span class="text-2xl">⌘</span>
                    </div>
                    <h2 class="mt-4 font-semibold">No organizations yet</h2>
                    <p class="mt-2 text-sm text-gray-400">
                        Create your first organization to start adding projects.
                    </p>
                </div>
            @endforelse
        </section>

        <aside class="h-fit rounded-xl border border-gray-800 bg-gray-900 p-6">
            <h2 class="text-lg font-semibold">Create an organization</h2>
            <p class="mt-2 text-sm leading-6 text-gray-400">
                Use an organization for your team, company, or personal projects.
            </p>

            <form method="POST" action="{{ route('organizations.store') }}"
                  class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="name" class="mb-2 block text-sm font-medium">
                        Organization name
                    </label>
                    <input id="name" name="name" type="text"
                           value="{{ old('name') }}" required maxlength="100"
                           placeholder="Acme Inc."
                           class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 outline-none focus:border-violet-500">
                    @error('name')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-violet-600 px-4 py-2.5 font-medium hover:bg-violet-500">
                    Create organization
                </button>
            </form>
        </aside>
    </div>
@endsection