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
            'address' => (string) ($profile['address'] ?? ''),
            'latitude' => isset($profile['latitude']) ? (float) $profile['latitude'] : null,
            'longitude' => isset($profile['longitude']) ? (float) $profile['longitude'] : null,
            'hours' => is_array($profile['hours'] ?? null) ? $profile['hours'] : [],
            'special_hours' => is_array($profile['special_hours'] ?? null) ? $profile['special_hours'] : [],
            'services' => is_array($profile['services'] ?? null) ? $profile['services'] : [],
            'prices' => is_array($profile['prices'] ?? null) ? $profile['prices'] : [],
            'gallery' => is_array($profile['gallery'] ?? null) ? $profile['gallery'] : [],
            'employees' => is_array($profile['employees'] ?? null) ? $profile['employees'] : [],
            'social_profiles' => is_array($profile['social_profiles'] ?? null) ? $profile['social_profiles'] : [],
            'offers' => is_array($profile['offers'] ?? null) ? $profile['offers'] : [],
            'faq' => is_array($profile['faq'] ?? null) ? $profile['faq'] : [],
            'trust' => [
                'verification_status' => (string) ($profile['verification_status'] ?? 'unverified'),
                'average_rating' => (float) ($profile['average_rating'] ?? 0),
                'review_count' => (int) ($profile['review_count'] ?? 0),
            ],
        ];
    }
}
