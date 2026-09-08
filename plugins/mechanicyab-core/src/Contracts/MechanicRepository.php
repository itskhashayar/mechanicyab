<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

interface MechanicRepository
{
    /** @return array<string, mixed>|null */
    public function find(int $id): ?array;

    /** @return array<string, mixed>|null */
    public function findPublicBySlug(string $slug): ?array;

    /** @param array<string, mixed> $data */
    public function create(array $data): int;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool;
}
