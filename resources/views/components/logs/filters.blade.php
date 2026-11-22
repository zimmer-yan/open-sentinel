@props([
    'organization',
    'projects',
    'selectedProjects',
])

<form method="GET"
      action="{{ route('organizations.logs.index', $organization) }}"
      class="mb-6 rounded-xl border border-gray-800 bg-gray-900 p-4">

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">

        {{-- Projects --}}
        <x-open-sentinel::project-multiselect
            :projects="$projects"
            :selected="$selectedProjects"
        />

        {{-- Search --}}
        <div>
            <label for="search"
                   class="mb-2 block text-sm font-medium text-gray-300">
                Search
            </label>

            <input id="search"
                   type="search"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search log messages..."
                   class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-sm outline-none focus:border-violet-500">
        </div>

        {{-- Level --}}
        <div>
            <label for="level"
                   class="mb-2 block text-sm font-medium text-gray-300">
                Level
            </label>

            <select id="level"
                    name="level"
                    class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-sm outline-none focus:border-violet-500">

                <option value="">All levels</option>

                @foreach ([
                    'debug' => 'Debug',
                    'info' => 'Info',
                    'notice' => 'Notice',
                    'warning' => 'Warning',
                    'error' => 'Error',
                    'critical' => 'Critical',
                    'alert' => 'Alert',
                    'emergency' => 'Emergency',
                ] as $value => $label)

                    <option value="{{ $value }}"
                        @selected(request('level') === $value)>
                        {{ $label }}
                    </option>

                @endforeach
            </select>
        </div>

        {{-- Environment --}}
        <div>
            <label for="environment"
                   class="mb-2 block text-sm font-medium text-gray-300">
                Environment
            </label>

            <input id="environment"
                   type="text"
                   name="environment"
                   value="{{ request('environment') }}"
                   placeholder="e.g. production"
                   class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-sm outline-none focus:border-violet-500">
        </div>

        {{-- Time range --}}
        <div>
            <label for="range"
                   class="mb-2 block text-sm font-medium text-gray-300">
                Time range
            </label>

            <select id="range"
                    name="range"
                    class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-sm outline-none focus:border-violet-500">

                <option value="">All time</option>

                <option value="1h" @selected(request('range') === '1h')>
                    Last hour
                </option>

                <option value="24h" @selected(request('range') === '24h')>
                    Last 24 hours
                </option>

                <option value="7d" @selected(request('range') === '7d')>
                    Last 7 days
                </option>

                <option value="30d" @selected(request('range') === '30d')>
                    Last 30 days
                </option>
            </select>
        </div>

    </div>

    <div class="mt-4 flex flex-wrap items-center gap-3">

        <button type="submit"
                class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium hover:bg-violet-500">
            Apply filters
        </button>

        <a href="{{ route('organizations.logs.index', $organization) }}"
           class="rounded-lg px-4 py-2 text-sm text-gray-400 hover:text-white">
            Clear filters
        </a>

    </div>
</form>
