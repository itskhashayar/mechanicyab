<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\MapAdapter;

final class OpenDirectionsAdapter implements MapAdapter
{
    public function directions(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): array
    {
        foreach ([$fromLatitude, $fromLongitude, $toLatitude, $toLongitude] as $coordinate) {
            if (!is_finite($coordinate)) {
                throw new \InvalidArgumentException('Coordinates must be finite.');
            }
        }
        if ($fromLatitude < -90 || $fromLatitude > 90 || $toLatitude < -90 || $toLatitude > 90 || $fromLongitude < -180 || $fromLongitude > 180 || $toLongitude < -180 || $toLongitude > 180) {
            throw new \InvalidArgumentException('Coordinates are outside valid geographic bounds.');
        }
        return [
            'url' => sprintf('https://www.openstreetmap.org/directions?from=%F,%F&to=%F,%F', $fromLatitude, $fromLongitude, $toLatitude, $toLongitude),
            'provider' => 'openstreetmap',
        ];
    }
}
