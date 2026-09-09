<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

interface ReferenceCatalogRepository
{
    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug, ?int $parentId = null): ?array;

    /** @param array<string, mixed> $record */
    public function save(array $record): int;
}
