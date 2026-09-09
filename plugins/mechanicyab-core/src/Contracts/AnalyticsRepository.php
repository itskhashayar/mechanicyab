<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

interface AnalyticsRepository
{
    /** @param array<string, mixed> $event */
    public function record(array $event): bool;
    /** @return list<array<string, mixed>> */
    public function daily(string $metricKey, string $from, string $to): array;
}
