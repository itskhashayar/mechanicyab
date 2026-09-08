<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class LocationTree
{
    /** @param list<array{id: int, parent_id: ?int, name: string, slug: string}> $rows @return list<array{id: int, parent_id: ?int, name: string, slug: string, children: list<array>}> */
    public function build(array $rows): array
    {
        $nodes = [];
        foreach ($rows as $row) {
            $nodes[(int) $row['id']] = [
                'id' => (int) $row['id'],
                'parent_id' => $row['parent_id'] === null ? null : (int) $row['parent_id'],
                'name' => (string) $row['name'],
                'slug' => (string) $row['slug'],
                'children' => [],
            ];
        }
        $roots = [];
        foreach (array_keys($nodes) as $id) {
            $parent = $nodes[$id]['parent_id'];
            if ($parent === null || !isset($nodes[$parent])) {
                $roots[] = &$nodes[$id];
                continue;
            }
            $nodes[$parent]['children'][] = &$nodes[$id];
        }
        unset($nodes);
        return $roots;
    }
}
