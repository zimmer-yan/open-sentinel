<?php

namespace OpenSentinel\Domain\Ingestion;

final readonly class ParsedEnvelope
{
    public function __construct(
        public array $headers,
        public array $items,
    ) {}

    public function toArray(): array
    {
        return [
            'headers' => $this->headers,
            'items' => $this->items,
        ];
    }
}