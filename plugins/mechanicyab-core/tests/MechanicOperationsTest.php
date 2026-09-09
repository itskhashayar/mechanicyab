<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Contracts\MechanicRepository;
use MechanicYab\Core\Contracts\MechanicSupportingRepository;
use MechanicYab\Core\Core\MechanicAuthorization;
use MechanicYab\Core\Core\MechanicOperationsService;
use PHPUnit\Framework\TestCase;

final class MechanicOperationsTest extends TestCase
{
    public function testOwnerCanPersistHoursAndSubmitVerification(): void
    {
        $mechanics = new OperationsMechanicRepository();
        $id = $mechanics->create(['owner_user_id' => 7, 'name' => 'One']);
        $supporting = new OperationsSupportingRepository();
        $service = new MechanicOperationsService($mechanics, $supporting);
        self::assertTrue($service->saveHours($id, ['day_of_week' => 1, 'is_open' => true, 'open_time' => '08:00', 'close_time' => '17:00'], 7));
        self::assertSame(1, $service->submitVerification($id, ['type' => 'license'], 7));
    }

    public function testVerificationTransitionIsValidatedAndPersisted(): void
    {
        $mechanics = new OperationsMechanicRepository();
        $id = $mechanics->create(['owner_user_id' => 7, 'name' => 'One']);
        $supporting = new OperationsSupportingRepository();
        $service = new MechanicOperationsService($mechanics, $supporting, new MechanicAuthorization(static fn (): bool => true));
        $verificationId = $service->submitVerification($id, ['type' => 'license'], 7);
        self::assertTrue($service->approveVerification($verificationId, 99));
        self::assertSame('approved', $supporting->findVerification($verificationId)['status']);
    }
}

final class OperationsMechanicRepository implements MechanicRepository
{
    private array $records = [];
    private int $next = 1;
    public function find(int $id): ?array { return $this->records[$id] ?? null; }
    public function findPublicBySlug(string $slug): ?array { return null; }
    public function create(array $data): int { $id = $this->next++; $this->records[$id] = $data + ['id' => $id]; return $id; }
    public function update(int $id, array $data): bool { $this->records[$id] = $data; return true; }
}

final class OperationsSupportingRepository implements MechanicSupportingRepository
{
    private array $hours = [];
    private array $verifications = [];
    private int $next = 1;
    public function saveHours(int $mechanicId, array $record): bool { $this->hours[$mechanicId] = $record; return true; }
    public function findHours(int $mechanicId, int $dayOfWeek): ?array { return $this->hours[$mechanicId] ?? null; }
    public function saveVerification(int $mechanicId, array $record): int { $id = $this->next++; $this->verifications[$id] = $record + ['id' => $id, 'mechanic_id' => $mechanicId]; return $id; }
    public function findVerification(int $verificationId): ?array { return $this->verifications[$verificationId] ?? null; }
    public function updateVerification(int $verificationId, string $status, int $reviewedBy): bool { $this->verifications[$verificationId]['status'] = $status; $this->verifications[$verificationId]['reviewed_by'] = $reviewedBy; return true; }
    public function saveGalleryItem(int $mechanicId, array $record): int { return 1; }
    public function saveProfileCollection(string $collection, int $mechanicId, array $record): int { return 1; }
    public function listProfileCollection(string $collection, int $mechanicId): array { return []; }
    public function deleteProfileCollection(string $collection, int $mechanicId, int $recordId): bool { return true; }
}
