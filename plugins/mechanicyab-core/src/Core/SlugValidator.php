<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class SlugValidator
{
    public function validate(string $slug): string
    {
        $slug = trim($slug);
        if ($slug === '' || mb_strlen($slug) > 220 || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new \InvalidArgumentException('Slug must be lowercase ASCII, URL-safe, and hyphen-separated.');
        }
        return $slug;
    }

    public function unique(string $slug, ?array $existing): string
    {
        $slug = $this->validate($slug);
        if ($existing !== null) {
            throw new \InvalidArgumentException('Slug already exists in the selected scope.');
        }
        return $slug;
    }
}
