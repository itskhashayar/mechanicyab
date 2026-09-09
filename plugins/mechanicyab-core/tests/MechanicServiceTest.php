<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Contracts\MechanicRepository;
use MechanicYab\Core\Core\HoursService;
use MechanicYab\Core\Core\MechanicAuthorization;
use MechanicYab\Core\Core\MechanicPublicResource;
use MechanicYab\Core\Core\MechanicService;
use MechanicYab\Core\Core\VerificationService;
use PHPUnit\Framework\TestCase;

final class MechanicServiceTest extends TestCase
{
    public function testOwnerCanCreateAndUpdateButCannotChangeProtectedFields(): void
    {
        $repository = new InMemoryMechanicRepository();
        $service = new MechanicService($repository);
        $id = $service->create(['name' => 'One', 'slug' => 'one', 'location_id' => 2, 'phone' => '1'], 7);
        self::assertSame(7, $repository->find($id)['owner_user_id']);
        $service->update($id, ['name' => 'Updated', 'owner_user_id' => 99, 'average_rating' => 5], 7);
        self::assertSame('Updated', $repository->find($id)['name']);
        self::assertSame(7, $repository->find($id)['owner_user_id']);
    }

    public function testNonOwnerCannotUpdate(): void
    {
        $repository = new InMemoryMechanicRepository();
        $service = new MechanicService($repository);
        $id = $service->create(['name' => 'One', 'slug' => 'one', 'location_id' => 2, 'phone' => '1'], 7);
        $this->expectException(\DomainException::class);
        $service->update($id, ['name' => 'Nope'], 8);
    }

    public function testHoursAndVerificationRulesAreStrict(): void
    {
        $hours = (new HoursService())->normalize(['day_of_week' => 1, 'is_open' => true, 'open_time' => '08:00', 'close_time' => '17:00']);
        self::assertSame('08:00:00', $hours['open_time']);
        self::assertTrue((new VerificationService())->canTransition('pending', 'approved'));
        self::assertFalse((new VerificationService())->canTransition('pending', 'expired'));
    }

    public function testPublicResourceHasTrustBoundary(): void
    {
        $data = (new MechanicPublicResource())->toResponse(['id' => 1, 'name' => 'One', 'owner_user_id' => 7, 'average_rating' => 4.5]);
        self::assertArrayNotHasKey('owner_user_id', $data);
        self::assertSame(4.5, $data['trust']['average_rating']);
    }
}

final class InMemoryMechanicRepository implements MechanicRepository
{
    private array $records = [];
    private int $nextId = 1;

    public function find(int $id): ?array { return $this->records[$id] ?? null; }
    public function findPublicBySlug(string $slug): ?array { foreach ($this->records as $record) { if ($record['slug'] === $slug) return $record; } return null; }
    public function create(array $data): int { $id = $this->nextId++; $data['id'] = $id; $this->records[$id] = $data; return $id; }
    public function update(int $id, array $data): bool { $this->records[$id] = $data; return true; }
}
