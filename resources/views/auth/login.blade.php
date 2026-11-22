@extends('open-sentinel::layouts.app')

@section('title', 'Sign in')

@section('content')
    <div class="mx-auto max-w-md py-10">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-semibold tracking-tight">Welcome back</h1>
            <p class="mt-2 text-sm text-gray-400">
                Sign in to continue to your workspace.
            </p>
        </div>

        <form method="POST" action="{{ route('login.store') }}"
              class="space-y-5 rounded-2xl border border-gray-800 bg-gray-900 p-6 sm:p-8">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email') }}" required autofocus
                       autocomplete="email"
                       class="w-full rounded-lg border border-gray-700 bg-gray-950 px-4 py-3 outline-none focus:border-violet-500">
                @error('email')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password"
                       required autocomplete="current-password"
                       class="w-full rounded-lg border border-gray-700 bg-gray-950 px-4 py-3 outline-none focus:border-violet-500">
                @error('password')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-400">
                <input type="checkbox" name="remember" value="1"
                       class="rounded border-gray-700 bg-gray-950">
                Remember me
            </label>

            <button type="submit"
                    class="w-full rounded-lg bg-violet-600 px-4 py-3 font-medium hover:bg-violet-500">
                Sign in
            </button>

            <p class="text-center text-sm text-gray-400">
                New here?
                <a href="{{ route('register') }}" class="text-violet-400 hover:text-violet-300">
                    Create an account
                </a>
            </p>
        </form>
    </div>
@endsection