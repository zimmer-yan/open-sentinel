<?php

namespace OpenSentinel\Http\Controllers;

use OpenSentinel\Models\Organization;
use OpenSentinel\Models\Issue;
use OpenSentinel\Models\Event;
use OpenSentinel\Models\LogEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        $organizations = $request->user()
            ->organizations()
            ->withCount('projects')
            ->orderBy('name')
            ->get();

        return view('open-sentinel::organizations.index', compact('organizations'));
    }

    public function show(
        Organization $organization,
        Request $request
    ): View {
        abort_unless(
            $organization->members()
                ->whereKey(auth()->id())
                ->exists(),
            403
        );

        $validated = $request->validate([
            'projects' => ['sometimes', 'array'],
            'projects.*' => ['integer'],
        ]);

        $projects = $organization->projects()
            ->orderBy('name')
            ->get();

        // Determine selected project IDs
        $selected = $validated['projects'] ?? $projects->pluck('id')->all();

        // Only allow projects belonging to this organization
        $selectedProjects = $projects
            ->whereIn('id', $selected)
            ->values();

        $selectedProjectIds = $selectedProjects->pluck('id');

        $selectedProjects->loadCount([
            'issues',
            'events',
            'logEntries',
        ]);

        $recentIssues = Issue::query()
            ->whereNot('status', '=', 'resolved')
            ->whereIn('project_id', $selectedProjectIds)
            ->withCount('events')
            ->with([
                'events' => fn ($query) => $query
                    ->orderByDesc('occurred_at')
                    ->orderByDesc('id')
                    ->limit(1),
            ])
            ->orderByDesc('last_seen_at')
            ->orderByDesc('id')
            ->limit(10)
            ->paginate(10);

        $recentEvents = Event::query()
            ->whereIn('project_id', $selectedProjectIds)
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->limit(10)
            ->paginate(10);

        $logs = LogEntry::query()
            ->whereIn('project_id', $selectedProjectIds)
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->paginate(50, ['*'], 'logs_page')
            ->withQueryString();

            #dd($selected);

        return view('open-sentinel::organizations.overview', [
            'organization' => $organization,
            'projects' => $projects,
            'selectedProjects' => $selectedProjects,
            'selected' => $selected,
            'recentIssues' => $recentIssues,
            'recentEvents' => $recentEvents,
            'logs' => $logs,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $organization = DB::transaction(function () use ($request, $validated) {
            $baseSlug = Str::slug($validated['name']) ?: 'organization';
            $slug = $baseSlug;
            $suffix = 1;

            while (Organization::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $suffix++;
            }

            $organization = Organization::create([
                'name' => $validated['name'],
                'slug' => $slug,
            ]);

            $organization->members()->attach($request->user()->id, [
                'role' => 'owner',
            ]);

            return $organization;
        });

        return redirect()->route('organizations.projects.index', $organization);
    }

    private function authorizeMembership(
        Request $request,
        Organization $organization,
    ): void {
        abort_unless(
            $organization->members()
                ->whereKey($request->user()->id)
                ->exists(),
            403,
        );
    }
}
