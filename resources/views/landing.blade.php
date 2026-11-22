@extends('open-sentinel::layouts.app')

@section('title', 'Open Sentinel')

@section('content')
    <section class="flex min-h-[70vh] items-center justify-center py-12">
        <div class="max-w-2xl text-center">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-violet-800 bg-violet-950/50 px-3 py-1.5 text-sm text-violet-300">
                <span class="h-2 w-2 rounded-full bg-violet-400"></span>
                Simple Monitoring
            </div>

            <h1 class="text-5xl font-semibold leading-tight tracking-tight sm:text-6xl">
                Open
                <span class="text-violet-400">Sentinel</span>
            </h1>

            <p class="mx-auto mt-6 max-w-lg text-lg leading-8 text-gray-400">
                Capture exceptions, group issues, and explore application
                events in one place. Spend less time debugging and more
                time building.
            </p>

            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="{{ route('register') }}"
                   class="rounded-lg bg-violet-600 px-6 py-3 font-medium hover:bg-violet-500">
                    Start monitoring
                </a>

                <a href="{{ route('login') }}"
                   class="rounded-lg border border-gray-700 px-6 py-3 font-medium hover:bg-gray-900">
                    Sign in
                </a>
            </div>
        </div>
    </section>
@endsection