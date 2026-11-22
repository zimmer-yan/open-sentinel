@extends('open-sentinel::layouts.project')

@section('title', 'Issues · ' . $organization->name)

@section('project-content')

    {{-- Page header --}}
    <x-open-sentinel::issues.header :organization="$organization" />

    {{-- Filters --}}
    <x-open-sentinel::issues.filters
        :organization="$organization"
        :projects="$projects"
        :selected-projects="$selectedProjects"
    />

    {{-- Issues --}}
    <x-open-sentinel::issues.list
        :issues="$issues"
        :organization="$organization"
    />

@endsection
