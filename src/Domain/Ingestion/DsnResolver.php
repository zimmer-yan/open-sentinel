<?php

namespace OpenSentinel\Domain\Ingestion;

use OpenSentinel\Models\Project;
use OpenSentinel\Models\ProjectKey;
use Illuminate\Http\Request;

final class DsnResolver
{
    public function resolveRequest(
        Request $request,
        int $projectId,
    ): ?Project {
        $key = $this->extractPublicKey($request);

        if (! $key) {
            return null;
        }

        $projectKey = ProjectKey::query()
            ->where('project_id', $projectId)
            ->where('public_key', $key)
            ->whereNull('revoked_at')
            ->with('project')
            ->first();

        if (! $projectKey) {
            return null;
        }

        $project = $projectKey->project;

        if (! $project || $project->status !== 'active') {
            return null;
        }

        // Avoid writing to the database on every request in a
        // later high-volume deployment; sample or throttle updates.
        $projectKey->forceFill([
            'last_used_at' => now(),
        ])->save();

        return $project;
    }

    private function extractPublicKey(Request $request): ?string
    {
        $queryKey = $request->query('sentry_key');

        if (is_string($queryKey) && $queryKey !== '') {
            return $queryKey;
        }

        $auth = $request->header('X-Sentry-Auth');

        if (is_string($auth)) {
            foreach (explode(',', $auth) as $part) {
                $pair = explode('=', trim($part), 2);

                if (
                    count($pair) === 2
                    && trim($pair[0]) === 'sentry_key'
                ) {
                    return trim($pair[1], " \t\n\r\0\x0B\"");
                }
            }
        }

        return null;
    }
}