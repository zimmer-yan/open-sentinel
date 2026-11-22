<?php

namespace OpenSentinel\Http\Controllers;

use OpenSentinel\Models\Organization;
use OpenSentinel\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use OpenSentinel\Models\ProjectKey;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use OpenSentinel\Models\Event;
use OpenSentinel\Models\Issue;
use OpenSentinel\Models\LogEntry;

class ProjectController extends Controller
{
    public function index(
        Request $request,
        Organization $organization,
    ): View {
        $this->authorizeMembership($request, $organization);

        $projects = $organization->projects()
            ->withCount(['issues', 'events'])
            ->orderBy('name')
            ->get();

        // Start at the beginning of the current hour.
        $start = now()->startOfHour()->subHours(23);
        $end = now()->startOfHour()->addHour();

        $projectIds = $projects->modelKeys();

        // Initialize all 24 buckets for every project.
        $activity = $projects->mapWithKeys(function ($project) use ($start) {
            $hours = [];

            for ($i = 0; $i < 24; $i++) {
                $hour = $start->copy()->addHours($i);
                $hours[$hour->format('Y-m-d H:00:00')] = 0;
            }

            return [
                $project->id => $hours,
            ];
        });

        if ($projectIds !== []) {
            $driver = DB::connection()->getDriverName();

            $hourExpression = match ($driver) {
                'sqlite' => "strftime('%Y-%m-%d %H:00:00', occurred_at)",
                'mysql', 'mariadb' => "DATE_FORMAT(occurred_at, '%Y-%m-%d %H:00:00')",
                'pgsql' => "date_trunc('hour', occurred_at)",
                default => throw new \RuntimeException(
                    "Unsupported database driver for activity chart: {$driver}"
                ),
            };

            $rows = Event::query()
                ->whereIn('project_id', $projectIds)
                ->where('occurred_at', '>=', $start)
                ->where('occurred_at', '<', $end)
                ->select('project_id')
                ->selectRaw("{$hourExpression} as hour")
                ->selectRaw('COUNT(*) as total')
                ->groupBy('project_id')
                ->groupByRaw($hourExpression)
                ->get();

            foreach ($rows as $row) {
                $hour = Carbon::parse($row->hour)->format('Y-m-d H:00:00');

                $projectActivity = $activity->get($row->project_id);

                if ($projectActivity !== null && array_key_exists($hour, $projectActivity)) {
                    $projectActivity[$hour] = (int) $row->total;

                    $activity->put($row->project_id, $projectActivity);
                }
            }
        }

        $projects->each(function ($project) use ($activity) {
            $project->setAttribute(
                'activity_counts',
                array_values($activity[$project->id] ?? []),
            );

            $project->setAttribute(
                'events_last_24_hours',
                array_sum($activity[$project->id] ?? []),
            );
        });

        return view('open-sentinel::organizations.projects.index', compact(
            'organization',
            'projects',
        ));
    }

    public function store(
        Request $request,
        Organization $organization,
    ): RedirectResponse {
        $this->authorizeMembership($request, $organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'platform' => [
                'required',
                'string',
                'in:php,laravel,javascript,python,node,other',
            ],
        ]);

        $baseSlug = Str::slug($validated['name']) ?: 'project';
        $slug = $baseSlug;
        $suffix = 1;

        while ($organization->projects()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        $project = $organization->projects()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'platform' => $validated['platform'],
            'status' => 'active',
        ]);

        return redirect()->route('organizations.overview', [
            'organization' => $organization,
            'project' => $project,
        ]);
    }

    public function show2(
        Request $request,
        Organization $organization,
        Project $project,
    ): View {
        $this->authorizeMembership($request, $organization);

        // A project must belong to the organization in the URL.
        abort_unless(
            $project->organization_id === $organization->id,
            404,
        );

        $project->loadCount(['issues', 'events', 'logEntries']);

        $recentIssues = $project->issues()
            ->latest('last_seen_at')
            ->limit(8)
            ->get();

        $recentEvents = $project->events()
            ->latest('received_at')
            ->limit(10)
            ->get();

        return view('open-sentinel::organizations.overview', compact(
            'organization',
            'project',
            'recentIssues',
            'recentEvents',
        ));
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
