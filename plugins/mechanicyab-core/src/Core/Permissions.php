<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class Permissions
{
    public function register(): void
    {
        if (!function_exists('get_role')) {
            return;
        }
        $role = get_role('administrator');
        if ($role !== null) {
            $role->add_cap('mechanicyab_view_diagnostics');
            $role->add_cap('mechanicyab_manage_mechanics');
            $role->add_cap('mechanicyab_verify_mechanics');
        }
    }

    public function canViewDiagnostics(): bool
    {
        return function_exists('current_user_can') && current_user_can('mechanicyab_view_diagnostics');
    }
}
