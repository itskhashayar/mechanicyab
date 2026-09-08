<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Contracts\ModuleInterface;
use MechanicYab\Core\Contracts\ModuleMetadata;
use MechanicYab\Core\Core\ModuleRegistry;
use PHPUnit\Framework\TestCase;

final class ModuleRegistryTest extends TestCase
{
    public function testCoreModuleRegisters(): void
    {
        $registry = new ModuleRegistry();
        $registry->register(new class implements ModuleInterface {
            public function metadata(): ModuleMetadata { return new ModuleMetadata('core', 'Core', '0.1.0'); }
            public function register(): void {}
        });
        $registry->boot();
        self::assertSame('active', $registry->state('core'));
    }

    public function testDuplicateModuleIdsAreRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        $registry = new ModuleRegistry();
        $module = new class implements ModuleInterface {
            public function metadata(): ModuleMetadata { return new ModuleMetadata('core', 'Core', '0.1.0'); }
            public function register(): void {}
        };
        $registry->register($module);
        $registry->register($module);
    }

    public function testMissingDependenciesAreRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        $registry = new ModuleRegistry();
        $registry->register(new class implements ModuleInterface {
            public function metadata(): ModuleMetadata { return new ModuleMetadata('dependent', 'Dependent', '0.1.0', dependencies: ['missing']); }
            public function register(): void {}
        });
        $registry->boot();
    }

    public function testCyclesAreRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        $registry = new ModuleRegistry();
        foreach ([['a', ['b']], ['b', ['a']]] as [$id, $dependencies]) {
            $registry->register(new class($id, $dependencies) implements ModuleInterface {
                public function __construct(private string $id, private array $dependencies) {}
                public function metadata(): ModuleMetadata { return new ModuleMetadata($this->id, $this->id, '0.1.0', dependencies: $this->dependencies); }
                public function register(): void {}
            });
        }
        $registry->boot();
    }
}
