<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class VehicleCatalog
{
    public function __construct(private readonly SlugValidator $slugs = new SlugValidator()) {}

    /** @param array<string, mixed> $vehicle @return array<string, mixed> */
    public function normalize(array $vehicle, string $level): array
    {
        if (!in_array($level, ['brand', 'model', 'trim'], true)) {
            throw new \InvalidArgumentException('Unsupported vehicle catalog level.');
        }
        $vehicle['name'] = trim((string) ($vehicle['name'] ?? ''));
        if ($vehicle['name'] === '') {
            throw new \InvalidArgumentException('Vehicle name is required.');
        }
        $vehicle['slug'] = $this->slugs->validate((string) ($vehicle['slug'] ?? ''));
        if ($level !== 'brand' && (int) ($vehicle['parent_id'] ?? 0) < 1) {
            throw new \InvalidArgumentException('Vehicle parent is required.');
        }
        $vehicle['status'] = (string) ($vehicle['status'] ?? 'active');
        return $vehicle;
    }
}
