<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class MechanicProfile
{
    /** @param array<string, mixed> $mechanic @return array<string, mixed> */
    public function normalize(array $mechanic): array
    {
        $name = trim((string) ($mechanic['name'] ?? ''));
        $slug = trim((string) ($mechanic['slug'] ?? ''));
        $owner = (int) ($mechanic['owner_user_id'] ?? 0);
        $location = (int) ($mechanic['location_id'] ?? 0);
        $phone = trim((string) ($mechanic['phone'] ?? ''));
        if ($name === '' || $slug === '' || $owner < 1 || $location < 1 || $phone === '') {
            throw new \InvalidArgumentException('Mechanic name, slug, owner, location and phone are required.');
        }
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new \InvalidArgumentException('Mechanic slug is not URL-safe.');
        }
        $mechanic['name'] = $name;
        $mechanic['slug'] = $slug;
        $mechanic['owner_user_id'] = $owner;
        $mechanic['location_id'] = $location;
        $mechanic['phone'] = $phone;
        $mechanic['status'] = (string) ($mechanic['status'] ?? 'draft');
        $mechanic['publication_status'] = (string) ($mechanic['publication_status'] ?? 'draft');
        $mechanic['verification_status'] = (string) ($mechanic['verification_status'] ?? 'unverified');
        return $mechanic;
    }

    /** @param array<string, mixed> $mechanic @return array<string, mixed> */
    public function publicView(array $mechanic): array
    {
        return [
            'id' => (int) ($mechanic['id'] ?? 0),
            'name' => (string) ($mechanic['name'] ?? ''),
            'slug' => (string) ($mechanic['slug'] ?? ''),
            'description' => (string) ($mechanic['description'] ?? ''),
            'phone' => (string) ($mechanic['phone'] ?? ''),
            'location_id' => (int) ($mechanic['location_id'] ?? 0),
            'verification_status' => (string) ($mechanic['verification_status'] ?? 'unverified'),
            'average_rating' => (float) ($mechanic['average_rating'] ?? 0),
            'review_count' => (int) ($mechanic['review_count'] ?? 0),
        ];
    }
}
