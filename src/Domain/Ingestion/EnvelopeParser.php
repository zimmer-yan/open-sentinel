<?php

namespace OpenSentinel\Domain\Ingestion;

use JsonException;
use RuntimeException;

final class EnvelopeParser
{
    public function parse(string $body): ParsedEnvelope
    {
        $maxBytes = config('ingestion.max_bytes');

        if (strlen($body) > $maxBytes) {
            throw new RuntimeException('Envelope too large.');
        }

        $offset = 0;
        $header = $this->readJsonLine($body, $offset);

        $items = [];

        while ($offset < strlen($body)) {
            if (count($items) >= config('ingestion.max_items')) {
                throw new RuntimeException('Too many envelope items.');
            }

            $itemHeader = $this->readJsonLine($body, $offset);

            if (! is_array($itemHeader) || ! isset($itemHeader['type'])) {
                throw new RuntimeException('Invalid envelope item header.');
            }

            $length = $itemHeader['length'] ?? null;

            if ($length !== null) {
                if (
                    ! is_int($length)
                    || $length < 0
                    || $length > strlen($body) - $offset
                ) {
                    throw new RuntimeException('Invalid item length.');
                }

                $payload = substr($body, $offset, $length);
                $offset += $length;

                // Length-delimited payloads are followed by a delimiter.
                if (($body[$offset] ?? null) === "\n") {
                    $offset++;
                } elseif ($offset < strlen($body)) {
                    throw new RuntimeException(
                        'Invalid envelope item delimiter.',
                    );
                }
            } else {
                $payload = $this->readRemainingLine($body, $offset);
            }

            $items[] = $this->normalizeItem(
                $itemHeader,
                $payload,
            );
        }

        return new ParsedEnvelope($header, $items);
    }

    private function readJsonLine(string $body, int &$offset): array
    {
        $line = $this->readRemainingLine($body, $offset);

        try {
            $decoded = json_decode(
                $line,
                true,
                64,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $e) {
            throw new RuntimeException(
                'Invalid envelope JSON.',
                previous: $e,
            );
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('Expected a JSON object.');
        }

        return $decoded;
    }

    private function readRemainingLine(
        string $body,
        int &$offset,
    ): string {
        $newline = strpos($body, "\n", $offset);

        if ($newline === false) {
            $line = substr($body, $offset);
            $offset = strlen($body);
            return $line;
        }

        $line = substr($body, $offset, $newline - $offset);
        $offset = $newline + 1;

        return $line;
    }

    private function normalizeItem(
        array $header,
        string $payload,
    ): array {
        $type = $header['type'];

        $decoded = null;

        if (in_array($type, ['event', 'log', 'transaction'], true)) {
            try {
                $decoded = json_decode(
                    $payload,
                    true,
                    64,
                    JSON_THROW_ON_ERROR,
                );
            } catch (JsonException $e) {
                throw new RuntimeException(
                    'Invalid JSON item payload.',
                    previous: $e,
                );
            }

            if (! is_array($decoded)) {
                throw new RuntimeException('Expected JSON item payload.');
            }
        }

        return [
            'type' => $type,
            'kind' => match ($type) {
                'event' => 'event',
                'log' => 'log',
                default => 'unsupported',
            },
            'headers' => $header,
            'payload' => $decoded,
        ];
    }
}