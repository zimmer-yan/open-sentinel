@props([
    'projects',
    'selected' => [],
    'name' => 'projects[]',
    'label' => 'Projects',
])

<div
    x-data="{
        open: false,
        search: '',
        selected: @js(collect($selected)->map(fn($id) => (string) $id)->values()),
        projects: @js($projects->map(fn($p) => [
            'id' => (string) $p->id,
            'name' => $p->name,
        ])->values()),

        toggle(id) {
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(item => item !== id);
            } else {
                this.selected.push(id);
            }
        },

        get filteredProjects() {
            return this.projects.filter(project =>
                project.name.toLowerCase().includes(this.search.toLowerCase())
            );
        }
    }"
    @click.outside="open = false"
    class="relative"
>
    <label class="mb-2 block text-sm font-medium text-gray-300">
        {{ $label }}
    </label>

    {{-- Dropdown trigger --}}
    <button
        type="button"
        @click="open = !open"
        class="flex min-h-[42px] w-full items-center justify-between gap-3 rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-sm text-left hover:border-gray-600 focus:border-violet-500 focus:outline-none"
    >
        <span>
            <template x-if="selected.length === 0">
                <span class="text-gray-500">Select projects...</span>
            </template>

            <template x-if="selected.length > 0">
                <span
                    class="rounded-md bg-violet-500/15 px-2 py-1 text-xs font-medium text-violet-300"
                    x-text="selected.length + ' projects selected'"
                ></span>
            </template>
        </span>

        <svg
            class="h-4 w-4 shrink-0 text-gray-400 transition-transform"
            :class="{ 'rotate-180': open }"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path stroke-linecap="round" stroke-linejoin="round"
                  stroke-width="2" d="m19 9-7 7-7-7" />
        </svg>
    </button>

    {{-- Dropdown content --}}
    <div
        x-show="open"
        x-transition
        x-cloak
        class="absolute z-50 mt-2 w-full overflow-hidden rounded-xl border border-gray-700 bg-gray-900 shadow-xl"
    >
        {{-- Search --}}
        <div class="border-b border-gray-800 p-3">
            <input
                type="text"
                x-model="search"
                placeholder="Search projects..."
                class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-sm text-white placeholder-gray-500 outline-none focus:border-violet-500"
            >
        </div>

        {{-- Project list --}}
        <div class="max-h-60 overflow-y-auto p-2">
            <template x-for="project in filteredProjects" :key="project.id">
                <button
                    type="button"
                    @click="toggle(project.id)"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm hover:bg-gray-800"
                >
                    <span
                        class="flex h-4 w-4 items-center justify-center rounded border"
                        :class="selected.includes(project.id)
                            ? 'border-violet-500 bg-violet-500'
                            : 'border-gray-600 bg-gray-950'"
                    >
                        <svg
                            x-show="selected.includes(project.id)"
                            class="h-3 w-3 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="3" d="m5 12 4 4L19 6" />
                        </svg>
                    </span>

                    <span class="text-gray-200" x-text="project.name"></span>
                </button>
            </template>

            <p
                x-show="filteredProjects.length === 0"
                class="px-3 py-4 text-center text-sm text-gray-500"
            >
                No projects found.
            </p>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between border-t border-gray-800 px-3 py-2">
            <span
                class="text-xs text-gray-500"
                x-text="selected.length + ' selected'"
            ></span>

            <button
                type="button"
                @click="selected = []"
                class="text-xs text-gray-400 hover:text-white"
            >
                Clear selection
            </button>
        </div>
    </div>

    {{-- Hidden inputs submitted with the parent form --}}
    <template x-for="id in selected" :key="id">
        <input type="hidden" name="{{ $name }}" :value="id">
    </template>
</div>
