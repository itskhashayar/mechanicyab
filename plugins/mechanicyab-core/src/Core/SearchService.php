<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\SearchProvider;
use MechanicYab\Core\Contracts\SearchRequest;

final class SearchService
{
    public function __construct(private readonly SearchProvider $provider) {}

    /** @param array<string, mixed> $filters @return array{items: list<array<string, mixed>>, meta: array<string, mixed>} */
    public function search(string $query = '', array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return $this->provider->search(new SearchRequest($filters, $page, $perPage, trim($query)));
    }
}
