@props([
    'issues',
    'organization',
])

<section class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-gray-800 px-5 py-4">
        <div>
            <h2 class="font-semibold">Recent issues</h2>

            <p class="mt-1 text-sm text-gray-500">
                Issues across your selected projects
            </p>
        </div>

        <p class="text-sm text-gray-400">
            {{ number_format($issues->total()) }}
            {{ \Illuminate\Support\Str::plural('issue', $issues->total()) }}
        </p>
    </div>

    {{-- Issues --}}
    @forelse ($issues as $issue)

        <x-open-sentinel::issues.entry
            :issue="$issue"
            :organization="$organization"
        />

    @empty

        <div class="px-5 py-12 text-center">
            <h3 class="font-medium">No issues found</h3>

            <p class="mt-2 text-sm text-gray-400">
                Issues will appear here when your application reports errors.
            </p>
        </div>

    @endforelse

    {{-- Pagination --}}
    @if ($issues->hasPages())
        <div class="border-t border-gray-800 px-5 py-4">
            {{ $issues->withQueryString()->links() }}
        </div>
    @endif

</section>
