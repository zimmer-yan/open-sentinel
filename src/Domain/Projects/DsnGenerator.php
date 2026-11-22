<?php

namespace OpenSentinel\Domain\Projects;

use OpenSentinel\Models\Project;
use OpenSentinel\Models\ProjectKey;

final class DsnGenerator
{
    public function for(
        Project $project,
        ProjectKey $key,
    ): string {
        if ($key->project_id !== $project->id) {
            throw new \InvalidArgumentException(
                'The key does not belong to this project.',
            );
        }

        $base = rtrim(config('ingestion.url'), '/');
        $scheme = parse_url($base, PHP_URL_SCHEME);
        $host = parse_url($base, PHP_URL_HOST);

        if (! in_array($scheme, ['http', 'https'], true) || ! $host) {
            throw new \RuntimeException(
                'INGESTION_URL must be an absolute HTTP(S) URL.',
            );
        }

        $port = parse_url($base, PHP_URL_PORT);
        $authority = $host.($port ? ':'.$port : '');

        return sprintf(
            '%s://%s@%s/%d',
            $scheme,
            rawurlencode($key->public_key),
            $authority,
            $project->id,
        );
    }
}