<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

interface ModuleInterface
{
    public function metadata(): ModuleMetadata;

    public function register(): void;
}
