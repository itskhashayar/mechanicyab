<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class ReferenceImport
{
    public function __construct(private readonly SlugValidator $slugs = new SlugValidator()) {}

    /** @param list<array<string, mixed>> $rows @return array{valid: list<array<string, mixed>>, errors: list<array{row: int, message: string}>} */
    public function validate(array $rows, string $type, string $parentKey = ''): array
    {
        $valid = [];
        $errors = [];
        $seen = [];
        foreach ($rows as $index => $row) {
            try {
                $name = trim((string) ($row['name'] ?? ''));
                $slug = $this->slugs->validate((string) ($row['slug'] ?? ''));
                $parent = $parentKey === '' ? null : (int) ($row[$parentKey] ?? 0);
                if ($name === '' || ($parentKey !== '' && $parent < 1)) {
                    throw new \InvalidArgumentException('Required name or parent reference is missing.');
                }
                $identity = $type . '|' . ($parent ?? 0) . '|' . $slug;
                if (isset($seen[$identity])) {
                    throw new \InvalidArgumentException('Duplicate row in import batch.');
                }
                $seen[$identity] = true;
                $row['name'] = $name;
                $row['slug'] = $slug;
                $valid[] = $row;
            } catch (\Throwable $exception) {
                $errors[] = ['row' => (int) $index, 'message' => $exception->getMessage()];
            }
        }
        return ['valid' => $valid, 'errors' => $errors];
    }
}
