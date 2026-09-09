<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

interface ReviewRepository
{
    /** @param array<string, mixed> $data */
    public function create(array $data): int;

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array;

    public function updateModeration(int $id, string $status, int $actorId): bool;

    /** @param array<string, mixed> $data */
    public function createReport(array $data): int;

    public function rebuildMechanicSummary(int $mechanicId): bool;

    /** @param array<string, mixed> $data */
    public function createReply(array $data): int;

    /** @return array<string, mixed>|null */
    public function findReply(int $reviewId): ?array;
}
