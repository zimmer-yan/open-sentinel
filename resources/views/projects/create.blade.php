<x-app-layout>
    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-2">Create Project</h2>
            <p class="text-sm text-gray-600 mb-4">Organization: <strong>{{ $organization->name }}</strong></p>

            <form method="POST" action="{{ route('organizations.projects.store', $organization->slug) }}">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Project Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="platform" class="block text-sm font-medium text-gray-700">Platform / Language</label>
                    <select name="platform" id="platform" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="laravel">Laravel / PHP</option>
                        <option value="javascript">JavaScript / TypeScript</option>
                        <option value="python">Python</option>
                        <option value="other">Other</option>
                    </select>
                    @error('platform')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Create Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
