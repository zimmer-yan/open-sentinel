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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $project = $resolver->resolveRequest($request, $projectId);

        if (! $project) {
            return response()->json([
                'error' => 'Invalid project key',
            ], 401);
        }

        try {
            $body = $this->decodeRequestBody($request);
            $envelope = $parser->parse($body);

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

                    $payload = $item['payload'];
                    $payload['event_id'] ??= $envelope->headers['event_id'] ?? null;
                    Log::debug('Envelope payload:', ['payload' => $payload]);

                    if ($item['kind'] === 'event') {
                        $normalized = $eventNormalizer->normalize(
                            $payload,
                        );

                        $title = $normalized->message
                            ?: $normalized->exceptionType
                            ?: 'Unhandled error';

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

                        $event = Event::firstOrCreate(
                            [
                                'project_id' => $project->id,
                                'event_id' => $normalized->eventId,
                            ],
                            [
                                'issue_id' => $issue->id,

                                'occurred_at' => $normalized->occurredAt,
                                'received_at' => now(),

                                'platform' => $normalized->platform,
                                'level' => $normalized->level,
                                'message' => $normalized->message,
                                'environment' => $normalized->environment,
                                'release' => $normalized->release,

                                'exception_type' => $normalized->exceptionType,
                                'exception_value' => $normalized->exceptionValue,
                                'stacktrace' => $normalized->stacktrace,

                                'trace_id' => $normalized->traceId,
                                'span_id' => $normalized->spanId,

                                'runtime_name' => $normalized->runtimeName,
                                'runtime_version' => $normalized->runtimeVersion,
                                'server_name' => $normalized->serverName,

                                'sdk_name' => $normalized->sdkName,
                                'sdk_version' => $normalized->sdkVersion,

                                'contexts' => $normalized->contexts,
                                'payload' => $normalized->payload,
                                'request' => $normalized->request,
                                'breadcrumbs' => $normalized->breadcrumbs,
                            ],
                        );

                        if ($event->wasRecentlyCreated) {
                            $issue->increment('event_count');

                            // Refresh before comparing timestamps so we
                            // don't overwrite a newer last_seen_at value.
                            $issue->refresh();

                            if (
                                $issue->last_seen_at === null
                                || $normalized->occurredAt > $issue->last_seen_at
                            ) {
                                $issue->update([
                                    'last_seen_at' => $normalized->occurredAt,
                                ]);
                            }

                            if ($issue->status === 'resolved') {
                                $issue->update([
                                    'status' => 'regressed',
                                ]);
                            }

                            $accepted++;
                        }

                        continue;
                    }

                    if ($item['kind'] === 'log') {
                        $normalizedLogs = $logNormalizer->normalize($payload);


                        foreach ($normalizedLogs as $normalized) {
                            LogEntry::create([
                                'project_id' => $project->id,
                                'batch_id' => $envelope->headers['event_id'] ?? null,
                                'received_at' => now(),
                                ...$normalized,
                            ]);

                            $accepted++;
                        }
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
            Log::warning('Invalid envelope received', [
                'project_id' => $project->id,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Invalid envelope.',
            ], 400);
        } catch (Throwable $e) {
            Log::error('Unable to process envelope', [
                'project_id' => $project->id,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            report($e);

            return response()->json([
                'error' => 'Unable to process envelope.',
            ], 500);
        }
    }

    private function decodeRequestBody(Request $request): string
    {
        $body = $request->getContent();

        $contentEncoding = strtolower(
            trim($request->header('Content-Encoding', 'identity')),
        );

        if ($contentEncoding === '' || $contentEncoding === 'identity') {
            return $body;
        }

        if ($contentEncoding !== 'gzip') {
            throw new InvalidArgumentException(
                "Unsupported Content-Encoding: {$contentEncoding}",
            );
        }

        $decoded = gzdecode($body);

        if ($decoded === false) {
            throw new InvalidArgumentException(
                'Invalid gzip-compressed request body.',
            );
        }

        return $decoded;
    }
}
