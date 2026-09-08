<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class MechanicPublicResource
{
    /** @param array<string, mixed> $profile @return array<string, mixed> */
    public function toResponse(array $profile): array
    {
        return [
            'id' => (int) ($profile['id'] ?? 0),
            'name' => (string) ($profile['name'] ?? ''),
            'slug' => (string) ($profile['slug'] ?? ''),
            'description' => (string) ($profile['description'] ?? ''),
            'phone' => (string) ($profile['phone'] ?? ''),
            'location_id' => (int) ($profile['location_id'] ?? 0),
            'trust' => [
                'verification_status' => (string) ($profile['verification_status'] ?? 'unverified'),
                'average_rating' => (float) ($profile['average_rating'] ?? 0),
                'review_count' => (int) ($profile['review_count'] ?? 0),
            ],
        ];
    }
}
