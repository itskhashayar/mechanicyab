<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\MechanicRepository;

final class WpdbMechanicRepository implements MechanicRepository
{
    public function __construct(private readonly object $wpdb) {}

    public function find(int $id): ?array
    {
        $table = $this->table();
        $row = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$table} WHERE id = %d AND deleted_at IS NULL LIMIT 1", $id), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function findPublicBySlug(string $slug): ?array
    {
        $table = $this->table();
        $row = $this->wpdb->get_row($this->wpdb->prepare("SELECT id, name, slug, description, phone, location_id, verification_status, average_rating, review_count FROM {$table} WHERE slug = %s AND status = 'active' AND publication_status = 'published' AND deleted_at IS NULL LIMIT 1", $slug), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $now = gmdate('Y-m-d H:i:s');
        $payload = array_merge($this->filterData($data), ['created_at' => $now, 'updated_at' => $now]);
        $formats = array_fill(0, count($payload), '%s');
        foreach (['owner_user_id', 'location_id', 'profile_completion_percent', 'review_count'] as $key) {
            if (array_key_exists($key, $payload)) {
                $formats[array_search($key, array_keys($payload), true)] = '%d';
            }
        }
        if ($this->wpdb->insert($this->table(), $payload, $formats) === false) {
            throw new \RuntimeException('Mechanic could not be created.');
        }
        return (int) $this->wpdb->insert_id;
    }

    public function update(int $id, array $data): bool
    {
        $data = array_merge($this->filterData($data), ['updated_at' => gmdate('Y-m-d H:i:s')]);
        $formats = array_fill(0, count($data), '%s');
        foreach (['owner_user_id', 'location_id', 'profile_completion_percent', 'review_count'] as $key) {
            if (array_key_exists($key, $data)) {
                $formats[array_search($key, array_keys($data), true)] = '%d';
            }
        }
        return $this->wpdb->update($this->table(), $data, ['id' => $id], $formats, ['%d']) !== false;
    }

    private function table(): string
    {
        if (!isset($this->wpdb->prefix)) {
            throw new \RuntimeException('WordPress database prefix is unavailable.');
        }
        return $this->wpdb->prefix . 'my_mechanics';
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function filterData(array $data): array
    {
        $allowed = [
            'owner_user_id', 'name', 'slug', 'description', 'phone', 'secondary_phone',
            'whatsapp_phone', 'website_url', 'instagram_url', 'address', 'latitude',
            'longitude', 'location_id', 'status', 'publication_status', 'verification_status',
            'profile_completion_percent', 'average_rating', 'review_count', 'response_rate',
            'published_at', 'deleted_at',
        ];
        return array_intersect_key($data, array_flip($allowed));
    }
}
