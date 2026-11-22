<?php

namespace OpenSentinel\Domain\Ingestion;

use DateTimeImmutable;
use InvalidArgumentException;

final class EventNormalizer
{
    public function __construct(
        private readonly PayloadRedactor $redactor,
    ) {}

    public function normalize(array $event): NormalizedEvent
    {
        $eventId = strtolower((string) ($event['event_id'] ?? ''));

        if (! preg_match('/^[a-f0-9]{32}$/', $eventId)) {
            throw new InvalidArgumentException('Invalid event ID.');
        }

        $occurredAt = $this->normalizeTimestamp(
            $event['timestamp'] ?? null,
        );

        $level = $event['level'] ?? 'error';

        if (! in_array(
            $level,
            ['fatal', 'error', 'warning', 'info', 'debug'],
            true,
        )) {
            $level = 'error';
        }

        // Redact before extracting fields so structured columns
        // do not accidentally bypass payload redaction.
        $payload = $this->redactor->redact($event);

        $exception = data_get($payload, 'exception.values.0', []);
        $contexts = data_get($payload, 'contexts', []);
        $request = data_get($payload, 'request', []);
        $breadcrumbs = data_get($payload, 'breadcrumbs.values', []);
        $trace = data_get($contexts, 'trace', []);
        $runtime = data_get($contexts, 'runtime', []);
        $sdk = data_get($payload, 'sdk', []);

        $exceptionType = $this->nullableString(
            data_get($exception, 'type'),
            255,
        );

        $exceptionValue = $this->nullableString(
            data_get($exception, 'value'),
            (int) config('ingestion.max_message_bytes', 16384),
        );

        $message = $this->nullableString(
            $payload['message'] ?? $exceptionValue,
            (int) config('ingestion.max_message_bytes', 16384),
        );

        $frames = data_get($exception, 'stacktrace.frames');

        if (! is_array($frames)) {
            $frames = null;
        }

        return new NormalizedEvent(
            eventId: $eventId,
            occurredAt: $occurredAt,
            level: $level,
            message: $message,
            environment: $this->nullableString(
                $payload['environment'] ?? null,
                128,
            ),
            release: $this->nullableString(
                $payload['release'] ?? null,
                255,
            ),

            platform: $this->nullableString(
                $payload['platform'] ?? null,
                64,
            ),
            exceptionType: $exceptionType,
            exceptionValue: $exceptionValue,
            stacktrace: $frames,

            traceId: $this->nullableString(
                data_get($trace, 'trace_id'),
                32,
            ),
            spanId: $this->nullableString(
                data_get($trace, 'span_id'),
                16,
            ),

            runtimeName: $this->nullableString(
                data_get($runtime, 'name'),
                255,
            ),
            runtimeVersion: $this->nullableString(
                data_get($runtime, 'version'),
                255,
            ),
            serverName: $this->nullableString(
                $payload['server_name'] ?? null,
                255,
            ),

            sdkName: $this->nullableString(
                data_get($sdk, 'name'),
                255,
            ),
            sdkVersion: $this->nullableString(
                data_get($sdk, 'version'),
                255,
            ),

            contexts: is_array($contexts) ? $contexts : [],
            request: is_array($request) ? $request : [],
            breadcrumbs: is_array($breadcrumbs) ? $breadcrumbs : [],
            payload: $payload,
        );
    }

    private function normalizeTimestamp(mixed $timestamp): DateTimeImmutable
    {
        if ($timestamp === null) {
            return new DateTimeImmutable();
        }

        if (
            ! is_numeric($timestamp)
            || ! is_finite((float) $timestamp)
        ) {
            throw new InvalidArgumentException(
                'Invalid event timestamp.',
            );
        }

        try {
            return new DateTimeImmutable('@'.(string) $timestamp);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException(
                'Invalid event timestamp.',
                previous: $e,
            );
        }
    }

    private function nullableString(
        mixed $value,
        int $maxLength,
    ): ?string {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $maxLength);
    }
}
