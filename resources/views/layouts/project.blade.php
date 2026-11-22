
@extends('open-sentinel::layouts.app')

@section('content-wrapper-class', 'w-full p-0')

@section('content')
    <div class="flex min-h-[calc(100vh-4rem)]">
        {{-- Sidebar --}}
        <aside class="w-40 shrink-0 border-r border-gray-800 bg-gray-950 p-5">

            <nav class="space-y-1">
                <a href="{{ route('organizations.overview', [$organization]) }}"
                   class="block rounded-lg px-3 py-2 text-sm text-gray-300 hover:bg-gray-800 hover:text-white">
                    Overview
                </a>

                <a href="{{ route('organizations.issues.index', [$organization]) }}"
                   class="block rounded-lg px-3 py-2 text-sm text-gray-300 hover:bg-gray-800 hover:text-white">
                    Issues
                </a>

                <a href="{{ route('organizations.logs.index', [$organization]) }}"
                   class="block rounded-lg px-3 py-2 text-sm text-gray-300 hover:bg-gray-800 hover:text-white">
                    Logs
                </a>
            </nav>
        </aside>

        {{-- Page content --}}
        <main class="min-w-0 flex-1 p-6 lg:p-8">
            @yield('project-content')
        </main>
    </div>
@endsection
