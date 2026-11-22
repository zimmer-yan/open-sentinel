<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sentinel') · Sentinel</title>
    @vite(['resources/css/open-sentinel.css', 'resources/js/open-sentinel-app.js'])
    <link
        rel="stylesheet"
        href="{{ asset('vendor/open-sentinel/css/open-sentinel.css') }}"
    >
</head>
<body class="min-h-screen bg-gray-950 text-gray-100 antialiased">
    <header class="border-b border-gray-800 bg-gray-950">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold tracking-tight">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-600 text-sm font-bold">&lt;S&gt;</span>
                <span>Sentinel</span>
            </a>

            @auth
                <nav class="flex items-center gap-4 text-sm">
                    <a href="{{ route('organizations.index') }}"
                       class="text-gray-300 hover:text-white">
                        Organizations
                    </a>

                    <span class="hidden text-gray-600 sm:inline">
                        {{ auth()->user()->name }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-gray-400 hover:text-white">
                            Sign out
                        </button>
                    </form>
                </nav>
            @else
                <nav class="flex items-center gap-4 text-sm">
                    <a href="{{ route('login') }}" class="text-gray-300 hover:text-white">
                        Sign in
                    </a>
                    <a href="{{ route('register') }}"
                       class="rounded-lg bg-violet-600 px-4 py-2 font-medium hover:bg-violet-500">
                        Get started
                    </a>
                </nav>
            @endauth
        </div>
    </header>

    <main class="@yield('content-wrapper-class', 'mx-auto max-w-7xl px-6 py-10')">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-emerald-800 bg-emerald-950/50 p-4 text-sm text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    <script
        src="{{ asset('vendor/open-sentinel/js/open-sentinel-app.js') }}"
        defer
    ></script>
</body>
</html>
@pushOnce('css')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endPushOnce
