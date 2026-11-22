<?php

namespace OpenSentinel\Http\Controllers;

use OpenSentinel\Models\Organization;
use OpenSentinel\Models\Project;
use OpenSentinel\Models\ProjectKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProjectConnectionController extends Controller
{
    public function show(
        Request $request,
        Organization $organization,
        Project $project,
    ): View {
        $this->authorizeProject($request, $organization, $project);

        $keys = $project->keys()
            ->latest()
            ->get();

        $activeKey = $keys->first(
            fn (ProjectKey $key) => $key->revoked_at === null
        );

        return view('open-sentinel::organizations.projects.connection', [
            'organization' => $organization,
            'project' => $project,
            'keys' => $keys,
            'activeKey' => $activeKey,
            'endpoint' => url('/'),
        ]);
    }

    public function rotate(
        Request $request,
        Organization $organization,
        Project $project,
    ): RedirectResponse {
        $this->authorizeProject($request, $organization, $project);

        DB::transaction(function () use ($project) {
            // Revoke existing active keys before creating a replacement.
            $project->keys()
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            $project->keys()->create([
                'public_key' => bin2hex(random_bytes(20)),
            ]);
        });

        return redirect()
            ->route('organizations.projects.connection', [$organization, $project])
            ->with('status', 'A new project key has been generated.');
    }

    public function revoke(
        Request $request,
        Organization $organization,
        Project $project,
        ProjectKey $key,
    ): RedirectResponse {
        $this->authorizeProject($request, $organization, $project);

        abort_unless($key->project_id === $project->id, 404);

        if ($key->revoked_at === null) {
            $key->forceFill([
                'revoked_at' => now(),
            ])->save();
        }

        return redirect()
            ->route('organizations.projects.connection', [$organization, $project])
            ->with('status', 'Project key revoked.');
    }

    private function authorizeProject(
        Request $request,
        Organization $organization,
        Project $project,
    ): void {
        abort_unless(
            $organization->members()
                ->whereKey($request->user()->id)
                ->exists(),
            403,
        );

        abort_unless(
            $project->organization_id === $organization->id,
            404,
        );
    }
}