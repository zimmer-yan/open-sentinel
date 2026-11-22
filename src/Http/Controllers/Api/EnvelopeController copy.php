<?php

namespace OpenSentinel\Http\Controllers\Api;

use OpenSentinel\Domain\Ingestion\DsnResolver;
use OpenSentinel\Domain\Ingestion\EnvelopeParser;
use OpenSentinel\Domain\Ingestion\EventNormalizer;
use OpenSentinel\Domain\Ingestion\LogNormalizer;
use OpenSentinel\Http\Controllers\Controller;
use OpenSentinel\Models\Event;
use OpenSentinel\Models\Issue;
use OpenSentinel\Models\LogEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class EnvelopeController extends Controller
{
    public function __invoke(
        Request $request,
        int $projectId,
        DsnResolver $resolver,
        EnvelopeParser $parser,
        EventNormalizer $eventNormalizer,
        LogNormalizer $logNormalizer,
    ): JsonResponse {
        Log::info('INGEST 1: request entered', [
            'project_id' => $projectId,
            'bytes' => strlen($request->getContent()),
        ]);

        $project = $resolver->resolveRequest($request, $projectId);

        Log::info('INGEST 2: key resolved', [
            'resolved' => $project !== null,
        ]);

        if (! $project) {
            return response()->json(['error' => 'Invalid project key'], 401);
        }

        Log::info('INGEST 3: parsing envelope');

        try {
            $envelope = $parser->parse($request->getContent());

            Log::info('INGEST 4: envelope parsed', [
                'items' => count($envelope->items),
            ]);

            $accepted = DB::transaction(function () use (
                $envelope,
                $project,
                $eventNormalizer,
                $logNormalizer,
            ): int {
                $accepted = 0;

                foreach ($envelope->items as $item) {
                    if (! is_array($item['payload'] ?? null)) {
                        continue;
                    }

                    if ($item['kind'] === 'event') {
                        $normalized = $eventNormalizer->normalize(
                            $item['payload'],
                        );

                        $title = $normalized->message ?: 'Unhandled error';
                        $fingerprint = hash('sha256', $title);

                        $issue = Issue::firstOrCreate(
                            [
                                'project_id' => $project->id,
                                'fingerprint_hash' => $fingerprint,
                            ],
                            [
                                'title' => Str::limit($title, 1000, ''),
                                'type' => 'error',
                                'status' => 'unresolved',
                                'first_seen_at' => $normalized->occurredAt,
                                'last_seen_at' => $normalized->occurredAt,
                                'event_count' => 0,
                            ],
                        );

                        $created = Event::firstOrCreate(
                            [
                                'project_id' => $project->id,
                                'event_id' => $normalized->eventId,
                            ],
                            [
                                'issue_id' => $issue->id,
                                'occurred_at' => $normalized->occurredAt,
                                'received_at' => now(),
                                'platform' => $item['payload']['platform'] ?? null,
                                'level' => $normalized->level,
                                'message' => $normalized->message,
                                'environment' => $normalized->environment,
                                'release' => $normalized->release,
                                'payload' => $normalized->payload,
                            ],
                        );

                        if ($created->wasRecentlyCreated) {
                            $issue->increment('event_count');

                            $issue->update([
                                'last_seen_at' => max(
                                    $issue->last_seen_at,
                                    $normalized->occurredAt,
                                ),
                            ]);

                            $accepted++;
                        }

                        continue;
                    }

                    if ($item['kind'] === 'log') {
                        $normalized = $logNormalizer->normalize(
                            $item['payload'],
                        );

                        LogEntry::create([
                            'project_id' => $project->id,
                            'batch_id' => $envelope->headers['event_id'] ?? null,
                            'received_at' => now(),
                            ...$normalized,
                        ]);

                        $accepted++;
                    }
                }

                return $accepted;
            });
            Log::info('Envelope processed', [
                'project_id' => $project->id,
                'accepted' => $accepted,
            ]);

            return response()->json([
                'success' => true,
                'accepted' => $accepted,
            ], 202);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json([
                'error' => 'Invalid envelope.',
            ], 400);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error' => 'Unable to process envelope.',
            ], 500);
        }
    }
}