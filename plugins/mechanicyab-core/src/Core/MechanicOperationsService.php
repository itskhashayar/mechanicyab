<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\MechanicRepository;
use MechanicYab\Core\Contracts\MechanicSupportingRepository;

final class MechanicOperationsService
{
    public function __construct(
        private readonly MechanicRepository $mechanics,
        private readonly MechanicSupportingRepository $supporting,
        private readonly MechanicAuthorization $authorization = new MechanicAuthorization(),
        private readonly HoursService $hours = new HoursService(),
        private readonly VerificationService $verification = new VerificationService(),
    ) {}

    /** @param array<string, mixed> $data */
    public function saveHours(int $mechanicId, array $data, int $actorUserId): bool
    {
        $mechanic = $this->mechanics->find($mechanicId);
        if ($mechanic === null || !$this->authorization->canManage($mechanic, $actorUserId)) {
            throw new \DomainException('Mechanic ownership validation failed.');
        }
        return $this->supporting->saveHours($mechanicId, $this->hours->normalize($data));
    }

    /** @param array<string, mixed> $data */
    public function submitVerification(int $mechanicId, array $data, int $actorUserId): int
    {
        $mechanic = $this->mechanics->find($mechanicId);
        if ($mechanic === null || !$this->authorization->canManage($mechanic, $actorUserId)) {
            throw new \DomainException('Mechanic ownership validation failed.');
        }
        $data['submitted_by'] = $actorUserId;
        $data['status'] = 'pending';
        return $this->supporting->saveVerification($mechanicId, $data);
    }

    public function approveVerification(int $verificationId, int $actorUserId): bool
    {
        if (!$this->authorization->canVerify()) {
            throw new \DomainException('Verification capability is required.');
        }
        $record = $this->supporting->findVerification($verificationId);
        if ($record === null) {
            throw new \RuntimeException('Verification record not found.');
        }
        $status = $this->verification->transition((string) $record['status'], 'approved');
        return $this->supporting->updateVerification($verificationId, $status, $actorUserId);
    }
}
