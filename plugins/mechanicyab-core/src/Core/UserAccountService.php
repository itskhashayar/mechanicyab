<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\UserDataRepository;

final class UserAccountService
{
    public function __construct(private readonly UserDataRepository $repository) {}

    public function addFavorite(int $userId, string $entityType, int $entityId): bool
    {
        $this->assertUser($userId); if ($entityType !== 'mechanic' || $entityId < 1) { throw new \InvalidArgumentException('Only valid mechanic favorites are supported.'); }
        return $this->repository->addFavorite($userId, $entityType, $entityId);
    }
    public function removeFavorite(int $userId, string $entityType, int $entityId): bool { $this->assertUser($userId); return $this->repository->removeFavorite($userId, $entityType, $entityId); }
    public function favorites(int $userId): array { $this->assertUser($userId); return $this->repository->favorites($userId); }
    public function createVehicle(int $userId, array $data): int
    {
        $this->assertUser($userId);
        if ((int) ($data['brand_id'] ?? 0) < 1 || (int) ($data['model_id'] ?? 0) < 1) { throw new \InvalidArgumentException('Vehicle brand and model are required.'); }
        return $this->repository->createVehicle($userId, $data);
    }
    public function createReminder(int $userId, array $data): int
    {
        $this->assertUser($userId);
        if ((int) ($data['vehicle_id'] ?? 0) < 1 || (string) ($data['title'] ?? '') === '' || (empty($data['due_date']) && empty($data['due_mileage']))) { throw new \InvalidArgumentException('Reminder requires vehicle, title and date or mileage.'); }
        return $this->repository->createReminder($userId, $data);
    }
    public function notify(int $userId, string $type, string $title, string $body, array $data = []): int { $this->assertUser($userId); return $this->repository->notify($userId, $type, $title, $body, $data); }
    private function assertUser(int $userId): void { if ($userId < 1) { throw new \DomainException('Authenticated user is required.'); } }
}
