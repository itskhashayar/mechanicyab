<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\MechanicSupportingRepository;

final class MechanicGalleryService
{
    public function __construct(private readonly MechanicSupportingRepository $repository, private readonly \Closure $ownerChecker) {}

    /** @param array<string,mixed> $record */
    public function add(int $mechanicId, int $actorId, array $record): int
    {
        if (!(bool) ($this->ownerChecker)($mechanicId, $actorId)) { throw new \DomainException('Mechanic ownership is required.'); }
        $mediaId = (int) ($record['media_id'] ?? 0);
        if ($mechanicId < 1 || $mediaId < 1) { throw new \InvalidArgumentException('Mechanic and media are required.'); }
        if (function_exists('get_post_type') && get_post_type($mediaId) !== 'attachment') { throw new \InvalidArgumentException('Media must reference a WordPress attachment.'); }
        return $this->repository->saveGalleryItem($mechanicId, $record);
    }
}
