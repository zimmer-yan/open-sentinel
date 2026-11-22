@extends('open-sentinel::layouts.app')

@section('title', 'Create account')

@section('content')
    <div class="mx-auto max-w-md py-10">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-semibold tracking-tight">Create your account</h1>
            <p class="mt-2 text-sm text-gray-400">
                Set up your workspace and start monitoring.
            </p>
        </div>

        <form method="POST" action="{{ route('register.store') }}"
              class="space-y-5 rounded-2xl border border-gray-800 bg-gray-900 p-6 sm:p-8">
            @csrf

            <div>
                <label for="name" class="mb-2 block text-sm font-medium">Name</label>
                <input id="name" name="name" type="text"
                       value="{{ old('name') }}" required autofocus
                       autocomplete="name"
                       class="w-full rounded-lg border border-gray-700 bg-gray-950 px-4 py-3 outline-none focus:border-violet-500">
                @error('name')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="mb-2 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email') }}" required
                       autocomplete="email"
                       class="w-full rounded-lg border border-gray-700 bg-gray-950 px-4 py-3 outline-none focus:border-violet-500">
                @error('email')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password"
                       required autocomplete="new-password"
                       class="w-full rounded-lg border border-gray-700 bg-gray-950 px-4 py-3 outline-none focus:border-violet-500">
                @error('password')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation"
                       class="mb-2 block text-sm font-medium">
                    Confirm password
                </label>
                <input id="password_confirmation" name="password_confirmation"
                       type="password" required autocomplete="new-password"
                       class="w-full rounded-lg border border-gray-700 bg-gray-950 px-4 py-3 outline-none focus:border-violet-500">
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-violet-600 px-4 py-3 font-medium hover:bg-violet-500">
                Create account
            </button>

            <p class="text-center text-sm text-gray-400">
                Already have an account?
                <a href="{{ route('login') }}" class="text-violet-400 hover:text-violet-300">
                    Sign in
                </a>
            </p>
        </form>
    </div>
@endsection