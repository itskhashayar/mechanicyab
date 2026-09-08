<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

interface SearchProvider
{
    /** @return array{items: list<array<string, mixed>>, meta: array<string, mixed>} */
    public function search(SearchRequest $request): array;
}
