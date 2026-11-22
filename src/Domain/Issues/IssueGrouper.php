<?php

namespace OpenSentinel\Domain\Issues;

use OpenSentinel\Models\Event;
use OpenSentinel\Models\Issue;
use OpenSentinel\Models\Project;
use Illuminate\Support\Facades\DB;

final class IssueGrouper
{
    public function __construct(
        private readonly FingerprintGenerator $fingerprints,
    ) {}

    public function persist(
        Project $project,
        array $event,
        array $normalized,
    ): ?Event {
        return DB::transaction(function () use (
            $project,
            $event,
            $normalized,
        ) {
            $existing = Event::query()
                ->where('project_id', $project->id)
                ->where('event_id', $normalized['event_id'])
                ->first();

            if ($existing) {
                return null;
            }

            $fingerprint = $this->fingerprints->generate($event);

            $issue = Issue::query()->firstOrCreate(
                [
                    'project_id' => $project->id,
                    'fingerprint_hash' => $fingerprint,
                ],
                [
                    'title' => $this->titleFor($event),
                    'type' => 'error',
                    'status' => 'unresolved',
                    'first_seen_at' => $normalized['occurred_at'],
                    'last_seen_at' => $normalized['occurred_at'],
                    'event_count' => 0,
                ],
            );

            $stored = Event::query()->create([
                'project_id' => $project->id,
                'issue_id' => $issue->id,
                'event_id' => $normalized['event_id'],
                'occurred_at' => $normalized['occurred_at'],
                'received_at' => now(),
                'platform' => $event['platform'] ?? 'php',
                'level' => $normalized['level'],
                'message' => $normalized['message'],
                'environment' => $normalized['environment'],
                'release' => $normalized['release'],
                'payload' => $normalized['payload'],
            ]);

            $issue->increment('event_count');

            $issue->refresh();

            if ($normalized['occurred_at']->greaterThan(
                $issue->last_seen_at,
            )) {
                $issue->update([
                    'last_seen_at' => $normalized['occurred_at'],
                ]);
            }

            return $stored;
        }, attempts: 3);
    }

    private function titleFor(array $event): string
    {
        $exception = $event['exception']['values'][0] ?? [];

        $type = $exception['type'] ?? 'Error';
        $message = $exception['value'] ?? ($event['message'] ?? '');

        return mb_substr(
            trim($type.': '.$message),
            0,
            255,
        );
    }
}