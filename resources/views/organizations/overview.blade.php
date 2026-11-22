@extends('open-sentinel::layouts.project')

@section('title', $organization->name . ' dashboard')

@section('project-content')

    {{-- Page header --}}
    <x-open-sentinel::projects.header :organization="$organization" />

    <form method="GET" action="{{ route('organizations.projects.index', $organization) }}" class="mb-8">
        <x-open-sentinel::project-multiselect
            :projects="$projects"
            :selected="$selected"
        />

        <button
            type="submit"
            class="mt-3 rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-medium hover:bg-violet-500"
        >
            Apply filters
        </button>
    </form>

    <x-open-sentinel::projects.stats :projects="$selectedProjects" />

    {{-- Issue entries --}}
    <div class="mt-8">
        <x-open-sentinel::issues.list
            :issues="$recentIssues"
            :organization="$organization"
        />
    </div>

    {{-- Log entries --}}
    <div class="mt-8">
        <x-open-sentinel::logs.list :logs="$logs" />
    </div>
@endsection
