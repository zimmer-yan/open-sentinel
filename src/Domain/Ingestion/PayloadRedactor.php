<?php

namespace OpenSentinel\Domain\Ingestion;

final class PayloadRedactor
{
    private const SENSITIVE_KEYS = [
        'password',
        'authorization',
        'cookie',
        'set-cookie',
        'access_token',
        'refresh_token',
        'api_key',
        'secret',
    ];

    public function redact(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $result = [];

        foreach ($value as $key => $item) {
            if (
                is_string($key)
                && in_array(strtolower($key), self::SENSITIVE_KEYS, true)
            ) {
                $result[$key] = '[Filtered]';
                continue;
            }

            $result[$key] = $this->redact($item);
        }

        return $result;
    }
}