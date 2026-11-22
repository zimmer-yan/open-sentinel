@props([
    'organization',
    'projects',
    'selectedProjects',
])

<form method="GET"
      action="{{ route('organizations.issues.index', $organization) }}"
      class="mb-6 rounded-xl border border-gray-800 bg-gray-900 p-4">

    <div class="grid gap-4 sm:grid-cols-2">

        {{-- Projects --}}
        <x-open-sentinel::project-multiselect
            :projects="$projects"
            :selected="$selectedProjects"
        />

        {{-- Status --}}
        <div>
            <label for="status"
                   class="mb-2 block text-sm font-medium text-gray-300">
                Status
            </label>

            <select id="status"
                    name="status"
                    class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-sm outline-none focus:border-violet-500">

                <option value="">All issues</option>

                <option value="unresolved" @selected(request('status') !== null && (request('status', 'unresolved') === 'unresolved' || request('status', 'unresolved') === 'regressed'))>
                    Unresolved
                </option>

                <option value="resolved" @selected(request('status', 'unresolved') === 'resolved')>
                    Resolved
                </option>

            </select>
        </div>

    </div>

    <div class="mt-4 flex flex-wrap items-center gap-3">

        <button type="submit"
                class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium hover:bg-violet-500">
            Apply filters
        </button>

        <a href="{{ route('organizations.issues.index', $organization) }}"
           class="rounded-lg px-4 py-2 text-sm text-gray-400 hover:text-white">
            Clear filters
        </a>

    </div>
</form>
