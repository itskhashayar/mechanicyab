<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

interface MechanicSupportingRepository
{
    /** @param array<string, mixed> $record */
    public function saveHours(int $mechanicId, array $record): bool;

    /** @return array<string, mixed>|null */
    public function findHours(int $mechanicId, int $dayOfWeek): ?array;

    /** @param array<string, mixed> $record */
    public function saveVerification(int $mechanicId, array $record): int;

    /** @return array<string, mixed>|null */
    public function findVerification(int $verificationId): ?array;

    public function updateVerification(int $verificationId, string $status, int $reviewedBy): bool;

    /** @param array<string, mixed> $record */
    public function saveGalleryItem(int $mechanicId, array $record): int;
}
