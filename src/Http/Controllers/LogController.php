<?php

namespace OpenSentinel\Http\Controllers;

use OpenSentinel\Models\Event;
use OpenSentinel\Models\LogEntry;
use Illuminate\Http\Request;
use OpenSentinel\Models\Organization;
use OpenSentinel\Models\Project;
use Illuminate\Contracts\View\View;

class LogController extends Controller
{
    public function index(
        Request $request,
        Organization $organization,
    ): View {
        abort_unless(
            $organization->members()
                ->where('users.id', $request->user()->id)
                ->exists(),
            403,
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

        // Build logs query across selected projects
        $logs = LogEntry::query()
            ->with('project')
            ->whereIn('project_id', $selectedProjects)
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('message', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('level'), function ($query) use ($request) {
                $query->where('level', $request->level);
            })
            ->when($request->filled('environment'), function ($query) use ($request) {
                $query->where('environment', 'like', '%' . $request->environment . '%');
            })
            ->when($request->filled('range'), function ($query) use ($request) {
                $range = match ($request->range) {
                    '1h' => now()->subHour(),
                    '24h' => now()->subDay(),
                    '7d' => now()->subDays(7),
                    '30d' => now()->subDays(30),
                    default => null,
                };

                if ($range) {
                    $query->where('occurred_at', '>=', $range);
                }
            })
            ->orderByDesc('occurred_at')
            ->paginate(10)
            ->withQueryString();

        return view('open-sentinel::organizations.logs.index', compact(
            'organization',
            'projects',
            'selectedProjects',
            'logs',
        ));
    }
}