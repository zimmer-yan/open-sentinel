@extends('open-sentinel::layouts.project')

@section('title', 'Logs · ' . $organization->name)

@section('project-content')

    {{-- Page header --}}
    <x-open-sentinel::logs.header :organization="$organization" />

    {{-- Filters --}}
    <x-open-sentinel::logs.filters
        :organization="$organization"
        :projects="$projects"
        :selected-projects="$selectedProjects"
    />

    {{-- Log entries --}}
    <x-open-sentinel::logs.list :logs="$logs" />

@endsection
