<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class OrganicRanking
{
    /** @param array<string, mixed> $row @return array<string, mixed> */
    public function score(array $row, string $query = ''): array
    {
        $score = 0.0;
        if ($query !== '' && stripos((string) ($row['name'] ?? ''), $query) !== false) {
            $score += 1.0;
        }
        $score += min(1.0, ((float) ($row['average_rating'] ?? 0)) / 5.0) * 0.35;
        $score += min(1.0, ((int) ($row['review_count'] ?? 0)) / 100.0) * 0.15;
        $score += min(1.0, ((int) ($row['profile_completion_percent'] ?? 0)) / 100.0) * 0.15;
        $row['organic_score'] = round($score, 6);
        return $row;
    }
}
