<?php

namespace OpenSentinel\Http\Controllers;

use OpenSentinel\Models\Event;
use OpenSentinel\Models\Issue;
use OpenSentinel\Models\Organization;
use OpenSentinel\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    public function resolve(Organization $organization, Issue $issue)
    {
        $issue->update([
            'status' => 'resolved',
        ]);

        return back()->with('success', 'Issue resolved successfully.');
    }
    public function reopen(Organization $organization, Issue $issue)
    {
        $issue->update([
            'status' => 'unresolved',
        ]);

        return back()->with('success', 'Issue reopened successfully.');
    }

    public function index(
        Request $request,
        Organization $organization,
    ): View {
        abort_unless(
            $organization->members()
                ->where('users.id', auth()->id())
                ->exists(),
            403
        );
        // Get projects belonging to this organization
        $projects = $organization->projects()
            ->orderBy('name')
            ->get();

        // Get selected project IDs
        $selectedProjects = $request->input('projects', []);

        // Validate that selected projects belong to this organization
        $selectedProjects = $request->has('projects')
            ? $request->input('projects', [])
            : $projects->pluck('id')->toArray();

        // Get selected status (default: unresolved)
        $status = $request->input('status', 'unresolved') ?? '';

        if (!in_array($status, ['unresolved', 'resolved', 'regressed', ''], true)) {
            $status = 'unresolved';
        }

        // Build issues query
        $issuesQuery = Issue::whereIn('project_id', $selectedProjects)
            ->orderBy('created_at', 'desc');

        if ($status !== '') {
            $issuesQuery->where('status', $status);
        }

        $issues = $issuesQuery->paginate(10)
            ->withQueryString();
            
        return view("open-sentinel::organizations.issues.index", compact(
            'organization',
            'projects',
            'selectedProjects',
            'issues'
        ));
    }

    public function show(
        Organization $organization,
        Issue $issue
    ): View {
        abort_unless(
            $organization->members()
                ->where('users.id', auth()->id())
                ->exists(),
            403
        );

        $issue->loadCount('events');

        $latestEvent = $issue->events()
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->first();

        $events = $issue->events()
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('open-sentinel::organizations.issues.show', compact(
            'organization',
            'issue',
            'latestEvent',
            'events',
        ));
    }
}