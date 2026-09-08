<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\ReviewRepository;

final class WpdbReviewRepository implements ReviewRepository
{
    public function __construct(private readonly object $wpdb) {}

    public function create(array $data): int
    {
        $now = gmdate('Y-m-d H:i:s');
        $payload = [
            'mechanic_id' => (int) $data['mechanic_id'],
            'wp_user_id' => (int) $data['wp_user_id'],
            'vehicle_id' => isset($data['vehicle_id']) ? (int) $data['vehicle_id'] : null,
            'service_id' => isset($data['service_id']) ? (int) $data['service_id'] : null,
            'rating' => (int) $data['rating'],
            'quality_rating' => $data['quality_rating'] ?? null,
            'price_rating' => $data['price_rating'] ?? null,
            'speed_rating' => $data['speed_rating'] ?? null,
            'behavior_rating' => $data['behavior_rating'] ?? null,
            'title' => $data['title'] ?? null,
            'body' => (string) $data['body'],
            'status' => 'active',
            'moderation_status' => 'pending',
            'is_verified_visit' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        if ($this->wpdb->insert($this->table('reviews'), $payload, ['%d','%d','%d','%d','%d','%d','%d','%d','%d','%s','%s','%s','%s','%d','%s','%s']) === false) {
            throw new \RuntimeException('Review could not be created.');
        }
        return (int) $this->wpdb->insert_id;
    }

    public function find(int $id): ?array
    {
        $row = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$this->table('reviews')} WHERE id = %d AND deleted_at IS NULL LIMIT 1", $id), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function updateModeration(int $id, string $status, int $actorId): bool
    {
        $now = gmdate('Y-m-d H:i:s');
        return $this->wpdb->update($this->table('reviews'), ['moderation_status' => $status, 'published_at' => $status === 'approved' ? $now : null, 'updated_at' => $now], ['id' => $id], ['%s','%s','%s'], ['%d']) !== false;
    }

    public function createReport(array $data): int
    {
        $now = gmdate('Y-m-d H:i:s');
        $payload = ['review_id' => (int) $data['review_id'], 'reported_by' => (int) $data['reported_by'], 'reason_code' => (string) $data['reason_code'], 'description' => $data['description'] ?? null, 'status' => 'open', 'created_at' => $now, 'updated_at' => $now];
        if ($this->wpdb->insert($this->table('review_reports'), $payload, ['%d','%d','%s','%s','%s','%s','%s']) === false) {
            throw new \RuntimeException('Review report could not be created.');
        }
        return (int) $this->wpdb->insert_id;
    }

    public function rebuildMechanicSummary(int $mechanicId): bool
    {
        $summary = $this->wpdb->get_row($this->wpdb->prepare("SELECT AVG(rating) AS average_rating, COUNT(*) AS review_count FROM {$this->table('reviews')} WHERE mechanic_id = %d AND moderation_status = 'approved' AND status = 'active' AND deleted_at IS NULL", $mechanicId), ARRAY_A);
        if (!is_array($summary)) {
            return false;
        }
        return $this->wpdb->update($this->table('mechanics'), ['average_rating' => (float) ($summary['average_rating'] ?? 0), 'review_count' => (int) ($summary['review_count'] ?? 0), 'updated_at' => gmdate('Y-m-d H:i:s')], ['id' => $mechanicId], ['%f','%d','%s'], ['%d']) !== false;
    }

    private function table(string $name): string
    {
        if (!isset($this->wpdb->prefix)) {
            throw new \RuntimeException('WordPress database prefix is unavailable.');
        }
        return $this->wpdb->prefix . 'my_' . $name;
    }
}
