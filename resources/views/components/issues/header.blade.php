@props(['organization'])

<div class="mb-8">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm text-violet-400">
                {{ $organization->name }}
            </p>

            <h1 class="mt-2 text-3xl font-semibold tracking-tight">
                Issues
            </h1>
        </div>
    </div>
</div>
