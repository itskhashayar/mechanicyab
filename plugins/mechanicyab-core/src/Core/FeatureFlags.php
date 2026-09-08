<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class FeatureFlags
{
    public function enabled(string $flag, bool $default = false): bool
    {
        $value = (new Settings())->get('flag_' . $flag, $default);
        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
