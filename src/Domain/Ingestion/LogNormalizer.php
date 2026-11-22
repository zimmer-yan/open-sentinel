<?php

namespace OpenSentinel\Domain\Ingestion;

use InvalidArgumentException;

final class LogNormalizer
{
    /**
     * Normalize all log records in one Sentry log envelope item.
     *
     * @return list<array<string, mixed>>
     */
    public function normalize(array $envelopeItem): array
    {
        $items = $envelopeItem['items'] ?? null;

        if (! is_array($items)) {
            throw new InvalidArgumentException(
                'Invalid log envelope: missing items array.'
            );
        }

        $logs = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $rawAttributes = $item['attributes'] ?? [];
            $attributes = [];

            if (is_array($rawAttributes)) {
                foreach ($rawAttributes as $key => $attribute) {
                    if (! is_array($attribute)) {
                        continue;
                    }

                    $value = $attribute['value'] ?? null;

                    // Preserve the actual attribute value.
                    $attributes[$key] = $value;
                }
            }

            $timestamp = $item['timestamp'] ?? null;

            try {
                $occurredAt = $timestamp !== null
                    ? \Carbon\CarbonImmutable::createFromTimestamp(
                        (float) $timestamp,
                        'UTC',
                    )
                    : now()->toImmutable();
            } catch (\Throwable $e) {
                throw new \InvalidArgumentException(
                    'Invalid log timestamp.',
                    previous: $e,
                );
            }

            $logs[] = [
                'occurred_at' => $occurredAt,
                'level' => match ($item['level'] ?? 'info') {
                    'warn' => 'warning',
                    'fatal' => 'critical',
                    'trace' => 'debug',
                    default => $item['level'] ?? 'info',
                },
                'message' => isset($item['body']) && is_string($item['body'])
                    ? $item['body']
                    : null,
                'logger' => $attributes['logger'] ?? null,
                'environment' => $attributes['sentry.environment'] ?? null,
                'release' => $attributes['sentry.release'] ?? null,
                'attributes' => $attributes,
            ];
        }

        return $logs;
    }
}
