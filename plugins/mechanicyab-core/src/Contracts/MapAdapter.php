<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

interface MapAdapter
{
    /** @return array{url: string, provider: string} */
    public function directions(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): array;
}
