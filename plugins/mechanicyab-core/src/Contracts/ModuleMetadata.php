<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

final class ModuleMetadata
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $version,
        public readonly string $status = 'active',
        public readonly array $dependencies = [],
        public readonly array $permissions = [],
        public readonly array $routes = [],
        public readonly array $settingsSchema = [],
        public readonly int $migrationVersion = 0,
        public readonly array $events = [],
        public readonly array $analyticsEvents = [],
        public readonly ?string $healthCheck = null,
        public readonly array $featureFlags = [],
        public readonly array $adminNavigation = [],
        public readonly string $failurePolicy = 'degraded',
    ) {}

    public function validate(): void
    {
        if ($this->id === '' || !preg_match('/^[a-z][a-z0-9-]*$/', $this->id)) {
            throw new \InvalidArgumentException('Invalid module id.');
        }
        if ($this->name === '' || $this->version === '') {
            throw new \InvalidArgumentException('Module name and version are required.');
        }
        if (!in_array($this->status, ['active', 'disabled', 'blocked', 'degraded'], true)) {
            throw new \InvalidArgumentException('Invalid module status.');
        }
    }
}
