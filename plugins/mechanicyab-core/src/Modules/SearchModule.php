<?php

declare(strict_types=1);

namespace MechanicYab\Core\Modules;

use MechanicYab\Core\Contracts\ModuleInterface;
use MechanicYab\Core\Contracts\ModuleMetadata;

final class SearchModule implements ModuleInterface
{
    public function metadata(): ModuleMetadata
    {
        return new ModuleMetadata(
            id: 'search',
            name: 'Search & Map',
            version: '0.1.0',
            status: 'active',
            permissions: [],
            healthCheck: 'search',
            adminNavigation: [],
            failurePolicy: 'degraded',
        );
    }

    public function register(): void
    {
        // Routes are registered by the Plugin REST boundary; providers remain injectable.
    }
}
