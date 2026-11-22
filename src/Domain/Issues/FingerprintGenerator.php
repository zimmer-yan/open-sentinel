<?php

namespace OpenSentinel\Domain\Issues;

final class FingerprintGenerator
{
    public function generate(array $event): string
    {
        $exception = $event['exception']['values'][0] ?? [];

        $type = $exception['type'] ?? 'Error';
        $value = $exception['value'] ?? ($event['message'] ?? '');

        $frames = $exception['stacktrace']['frames'] ?? [];

        $inAppFrames = array_values(array_filter(
            $frames,
            fn (array $frame) => ($frame['in_app'] ?? false) === true,
        ));

        // Sentry stack traces commonly list frames oldest-first.
        // Use a few frames nearest to the throwing call.
        $framesForFingerprint = array_slice($inAppFrames, -3);

        $normalizedFrames = array_map(
            fn (array $frame) => implode(':', [
                $frame['filename'] ?? '',
                $frame['function'] ?? '',
            ]),
            $framesForFingerprint,
        );

        $canonical = json_encode([
            'type' => $type,
            'message' => $this->normalizeMessage((string) $value),
            'frames' => $normalizedFrames,
        ], JSON_THROW_ON_ERROR);

        return hash('sha256', $canonical);
    }

    private function normalizeMessage(string $message): string
    {
        $message = preg_replace('/\b\d+\b/', '*', $message) ?? $message;

        return mb_substr(trim($message), 0, 1000);
    }
}