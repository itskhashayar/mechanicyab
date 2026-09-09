<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class MechanicAuthorization
{
    public function __construct(private readonly ?\Closure $capabilityChecker = null) {}

    /** @param array<string, mixed> $mechanic */
    public function canManage(array $mechanic, int $userId): bool
    {
        return $userId > 0 && (int) ($mechanic['owner_user_id'] ?? 0) === $userId;
    }

    public function canVerify(): bool
    {
        if ($this->capabilityChecker !== null) {
            return (bool) ($this->capabilityChecker)('mechanicyab_verify_mechanics');
        }
        return function_exists('current_user_can') && current_user_can('mechanicyab_verify_mechanics');
    }
}
