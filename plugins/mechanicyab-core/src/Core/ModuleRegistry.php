<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\ModuleInterface;
use MechanicYab\Core\Contracts\ModuleMetadata;

final class ModuleRegistry
{
    /** @var array<string, ModuleInterface> */
    private array $modules = [];

    /** @var array<string, string> */
    private array $states = [];

    public function register(ModuleInterface $module): void
    {
        $metadata = $module->metadata();
        $metadata->validate();
        if (isset($this->modules[$metadata->id])) {
            throw new \RuntimeException('Duplicate module id: ' . $metadata->id);
        }
        $this->modules[$metadata->id] = $module;
        $this->states[$metadata->id] = $metadata->status;
    }

    public function boot(): void
    {
        $order = $this->resolveOrder();
        foreach ($order as $id) {
            $module = $this->modules[$id];
            $metadata = $module->metadata();
            if ($this->states[$id] !== 'active') {
                continue;
            }
            try {
                $module->register();
            } catch (\Throwable $exception) {
                $this->states[$id] = 'degraded';
                if ($metadata->failurePolicy === 'fail_closed') {
                    throw $exception;
                }
            }
        }
    }

    /** @return array<string, array{id: string, name: string, version: string, status: string, dependencies: array}> */
    public function all(): array
    {
        $result = [];
        foreach ($this->modules as $id => $module) {
            $metadata = $module->metadata();
            $result[$id] = [
                'id' => $id,
                'name' => $metadata->name,
                'version' => $metadata->version,
                'status' => $this->states[$id] ?? 'unknown',
                'dependencies' => $metadata->dependencies,
            ];
        }
        return $result;
    }

    public function state(string $id): string
    {
        return $this->states[$id] ?? 'unknown';
    }

    /** @return list<string> */
    private function resolveOrder(): array
    {
        $visiting = [];
        $visited = [];
        $order = [];
        foreach (array_keys($this->modules) as $id) {
            $this->visit($id, $visiting, $visited, $order);
        }
        return $order;
    }

    /** @param array<string, bool> $visiting @param array<string, bool> $visited @param list<string> $order */
    private function visit(string $id, array &$visiting, array &$visited, array &$order): void
    {
        if (isset($visited[$id])) {
            return;
        }
        if (isset($visiting[$id])) {
            throw new \RuntimeException('Module dependency cycle detected at: ' . $id);
        }
        $visiting[$id] = true;
        foreach ($this->modules[$id]->metadata()->dependencies as $dependency) {
            if (!isset($this->modules[$dependency])) {
                $this->states[$id] = 'blocked';
                throw new \RuntimeException(sprintf('Missing dependency "%s" for module "%s".', $dependency, $id));
            }
            $this->visit($dependency, $visiting, $visited, $order);
        }
        unset($visiting[$id]);
        $visited[$id] = true;
        $order[] = $id;
    }
}
