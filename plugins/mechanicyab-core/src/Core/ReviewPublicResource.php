<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class ReviewPublicResource
{
    /** @param array<string, mixed> $review @return array<string, mixed> */
    public function toResponse(array $review): array
    {
        return [
            'id' => (int) ($review['id'] ?? 0),
            'mechanic_id' => (int) ($review['mechanic_id'] ?? 0),
            'rating' => (int) ($review['rating'] ?? 0),
            'title' => (string) ($review['title'] ?? ''),
            'body' => (string) ($review['body'] ?? ''),
            'is_verified_visit' => (bool) ($review['is_verified_visit'] ?? false),
            'published_at' => $review['published_at'] ?? null,
        ];
    }
}
