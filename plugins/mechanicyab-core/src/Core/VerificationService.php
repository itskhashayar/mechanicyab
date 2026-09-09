<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class VerificationService
{
    private const TRANSITIONS = [
        'pending' => ['approved', 'rejected'],
        'rejected' => ['pending'],
        'approved' => ['suspended', 'expired'],
        'suspended' => ['pending'],
        'expired' => ['pending'],
    ];

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public function transition(string $from, string $to): string
    {
        if (!$this->canTransition($from, $to)) {
            throw new \InvalidArgumentException(sprintf('Invalid verification transition: %s -> %s.', $from, $to));
        }
        return $to;
    }
}
