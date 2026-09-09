<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

interface UserDataRepository
{
    public function addFavorite(int $userId, string $entityType, int $entityId): bool;
    public function removeFavorite(int $userId, string $entityType, int $entityId): bool;
    /** @return list<array<string, mixed>> */
    public function favorites(int $userId): array;
    /** @param array<string, mixed> $data */
    public function createVehicle(int $userId, array $data): int;
    /** @param array<string, mixed> $data */
    public function createReminder(int $userId, array $data): int;
    public function notify(int $userId, string $type, string $title, string $body, array $data = []): int;
}
