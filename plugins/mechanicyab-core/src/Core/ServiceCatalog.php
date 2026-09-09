<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class ServiceCatalog
{
    public function __construct(private readonly SlugValidator $slugs = new SlugValidator()) {}

    /** @param array<string, mixed> $service @return array<string, mixed> */
    public function normalize(array $service): array
    {
        $service['name'] = trim((string) ($service['name'] ?? ''));
        if ($service['name'] === '') {
            throw new \InvalidArgumentException('Service name is required.');
        }
        $service['slug'] = $this->slugs->validate((string) ($service['slug'] ?? ''));
        $service['category_id'] = max(1, (int) ($service['category_id'] ?? 0));
        $service['status'] = (string) ($service['status'] ?? 'active');
        return $service;
    }
}
