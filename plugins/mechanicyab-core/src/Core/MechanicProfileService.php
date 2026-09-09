<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\MechanicSupportingRepository;

final class MechanicProfileService
{
    /** @param \Closure(int): array<string,mixed> $mechanicResolver */
    public function __construct(private readonly MechanicSupportingRepository $repository, private readonly \Closure $mechanicResolver) {}
    /** @param array<string,mixed> $record */
    public function save(int $mechanicId, int $actorId, string $collection, array $record): int { $this->assertOwner($mechanicId,$actorId); if($mechanicId<1||trim($collection)===''){throw new \InvalidArgumentException('Mechanic and collection are required.');} return $this->repository->saveProfileCollection($collection,$mechanicId,$record); }
    /** @return list<array<string,mixed>> */
    public function list(int $mechanicId, int $actorId, string $collection): array { $this->assertOwner($mechanicId,$actorId); return $this->repository->listProfileCollection($collection,$mechanicId); }
    public function delete(int $mechanicId, int $actorId, string $collection, int $recordId): bool { $this->assertOwner($mechanicId,$actorId); return $this->repository->deleteProfileCollection($collection,$mechanicId,$recordId); }
    private function assertOwner(int $mechanicId,int $actorId): void { $mechanic = ($this->mechanicResolver)($mechanicId); if((int)($mechanic['owner_user_id'] ?? 0) !== $actorId){throw new \DomainException('Mechanic ownership is required.');} }
}
