<?php

namespace OpenSentinel\Jobs;

use OpenSentinel\Domain\Ingestion\EventNormalizer;
use OpenSentinel\Domain\Ingestion\LogNormalizer;
use OpenSentinel\Domain\Issues\IssueGrouper;
use OpenSentinel\Models\LogEntry;
use OpenSentinel\Models\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ProcessEnvelope implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 60;

    public function __construct(
        public readonly int $projectId,
        public readonly array $envelope,
    ) {
        $this->onQueue(config('ingestion.queue'));
    }

    public function backoff(): array
    {
        return [1, 5, 15, 60];
    }

    public function handle(
        EventNormalizer $events,
        LogNormalizer $logs,
        IssueGrouper $issues,
    ): void {
        $project = Project::query()
            ->findOrFail($this->projectId);

        foreach ($this->envelope['items'] as $item) {
            if (($item['kind'] ?? null) === 'event') {
                $this->processEvent(
                    $project,
                    $item,
                    $events,
                    $issues,
                );
            } elseif (($item['kind'] ?? null) === 'log') {
                $this->processLogs($project, $item, $logs);
            }
        }
    }

    private function processEvent(
        Project $project,
        array $item,
        EventNormalizer $normalizer,
        IssueGrouper $grouper,
    ): void {
        $payload = $item['payload'] ?? [];

        $normalized = $normalizer->normalize($payload);

        $grouper->persist(
            $project,
            $payload,
            [
                'event_id' => $normalized->eventId,
                'occurred_at' => $normalized->occurredAt,
                'level' => $normalized->level,
                'message' => $normalized->message,
                'environment' => $normalized->environment,
                'release' => $normalized->release,
                'payload' => $normalized->payload,
            ],
        );
    }

    private function processLogs(
        Project $project,
        array $item,
        LogNormalizer $normalizer,
    ): void {
        $payload = $item['payload'] ?? [];

        // This is the internal normalized log-batch format.
        $entries = $payload['entries'] ?? [$payload];

        if (
            ! is_array($entries)
            || count($entries) > config('ingestion.max_log_batch_size')
        ) {
            throw new \UnexpectedValueException(
                'Invalid log batch.',
            );
        }

        DB::transaction(function () use (
            $project,
            $entries,
            $normalizer,
            $item,
        ) {
            foreach ($entries as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $data = $normalizer->normalize($entry);

                LogEntry::query()->create([
                    'project_id' => $project->id,
                    'batch_id' => $item['headers']['item_id'] ?? null,
                    'occurred_at' => $data['occurred_at'],
                    'received_at' => now(),
                    'level' => $data['level'],
                    'message' => $data['message'],
                    'logger' => $data['logger'],
                    'environment' => $data['environment'],
                    'release' => $data['release'],
                    'attributes' => $data['attributes'],
                ]);
            }
        });
    }
}