<?php

declare(strict_types=1);

namespace MechanicYab\Core\Modules;

use MechanicYab\Core\Contracts\ModuleInterface;
use MechanicYab\Core\Contracts\ModuleMetadata;
use MechanicYab\Core\Core\Permissions;

final class CoreModule implements ModuleInterface
{
    public function metadata(): ModuleMetadata
    {
        return new ModuleMetadata(
            id: 'core',
            name: 'Core Platform',
            version: '0.1.0',
            status: 'active',
            permissions: ['mechanicyab_view_diagnostics'],
            healthCheck: 'core',
            adminNavigation: ['slug' => 'mechanicyab', 'title' => 'مکانیک‌یاب'],
            failurePolicy: 'fail_closed',
        );
    }

    public function register(): void
    {
        (new Permissions())->register();
    }
}
