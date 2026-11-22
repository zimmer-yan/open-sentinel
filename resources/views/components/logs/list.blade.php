@props(['logs'])

<section class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-800 px-5 py-4">
        <div>
            <h2 class="font-semibold">Log entries</h2>

            <p class="mt-1 text-sm text-gray-500">
                Newest entries first
            </p>
        </div>

        <p class="text-sm text-gray-400">
            {{ number_format($logs->total()) }}
            {{ \Illuminate\Support\Str::plural('entry', $logs->total()) }}
        </p>
    </div>

    {{-- Entries --}}
    @forelse ($logs as $log)

        <x-open-sentinel::logs.entry :log="$log" />

    @empty

        <div class="px-5 py-16 text-center">
            <div class="text-3xl">⌕</div>

            <h3 class="mt-3 font-semibold">
                No logs found
            </h3>

            <p class="mt-2 text-sm text-gray-400">
                Try changing your filters, or send a log from your application.
            </p>
        </div>

    @endforelse

    {{-- Pagination --}}
    @if ($logs->hasPages())
        <div class="border-t border-gray-800 px-5 py-4">
            {{ $logs->withQueryString()->links() }}
        </div>
    @endif

</section>