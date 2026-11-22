<?php

namespace OpenSentinel\Domain\Ingestion;

use DateTimeImmutable;

final readonly class NormalizedEvent
{
    public function __construct(
        public string $eventId,
        public DateTimeImmutable $occurredAt,
        public string $level,
        public ?string $message,
        public ?string $environment,
        public ?string $release,

        public ?string $platform,
        public ?string $exceptionType,
        public ?string $exceptionValue,
        public ?array $stacktrace,

        public ?string $traceId,
        public ?string $spanId,

        public ?string $runtimeName,
        public ?string $runtimeVersion,
        public ?string $serverName,

        public ?string $sdkName,
        public ?string $sdkVersion,

        public array $contexts,
        public array $payload,

        public array $request,
        public array $breadcrumbs,
    ) {}
}
