<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\MechanicRepository;

final class MechanicService
{
    public function __construct(
        private readonly MechanicRepository $repository,
        private readonly MechanicProfile $profiles = new MechanicProfile(),
        private readonly MechanicAuthorization $authorization = new MechanicAuthorization(),
    ) {}

    /** @param array<string, mixed> $data */
    public function create(array $data, int $actorUserId): int
    {
        if ($actorUserId < 1) {
            throw new \DomainException('Authenticated owner is required.');
        }
        $data['owner_user_id'] = $actorUserId;
        return $this->repository->create($this->profiles->normalize($data));
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data, int $actorUserId): bool
    {
        $existing = $this->repository->find($id);
        if ($existing === null || !$this->authorization->canManage($existing, $actorUserId)) {
            throw new \DomainException('Mechanic ownership validation failed.');
        }
        unset($data['owner_user_id'], $data['id'], $data['verification_status'], $data['average_rating'], $data['review_count']);
        return $this->repository->update($id, $this->profiles->normalize(array_merge($existing, $data)));
    }

    public function publicBySlug(string $slug): array
    {
        $record = $this->repository->findPublicBySlug($slug);
        if ($record === null) {
            throw new \RuntimeException('Mechanic not found.');
        }
        return $this->profiles->publicView($record);
    }
}
