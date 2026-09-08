<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\MechanicSupportingRepository;

final class WpdbMechanicSupportingRepository implements MechanicSupportingRepository
{
    public function __construct(private readonly object $wpdb) {}

    public function saveHours(int $mechanicId, array $record): bool
    {
        $payload = [
            'mechanic_id' => $mechanicId,
            'day_of_week' => (int) $record['day_of_week'],
            'is_open' => (int) $record['is_open'],
            'open_time' => $record['open_time'] ?? null,
            'close_time' => $record['close_time'] ?? null,
            'break_start' => $record['break_start'] ?? null,
            'break_end' => $record['break_end'] ?? null,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ];
        $existing = $this->wpdb->get_var($this->wpdb->prepare("SELECT id FROM {$this->table('mechanic_hours')} WHERE mechanic_id = %d AND day_of_week = %d LIMIT 1", $mechanicId, $payload['day_of_week']));
        if ($existing) {
            return $this->wpdb->update($this->table('mechanic_hours'), $payload, ['id' => (int) $existing], ['%d','%d','%d','%s','%s','%s','%s','%s'], ['%d']) !== false;
        }
        $payload['created_at'] = gmdate('Y-m-d H:i:s');
        return $this->wpdb->insert($this->table('mechanic_hours'), $payload, ['%d','%d','%d','%s','%s','%s','%s','%s','%s']) !== false;
    }

    public function findHours(int $mechanicId, int $dayOfWeek): ?array
    {
        $row = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$this->table('mechanic_hours')} WHERE mechanic_id = %d AND day_of_week = %d LIMIT 1", $mechanicId, $dayOfWeek), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function saveVerification(int $mechanicId, array $record): int
    {
        $payload = [
            'mechanic_id' => $mechanicId,
            'type' => (string) $record['type'],
            'status' => (string) ($record['status'] ?? 'pending'),
            'submitted_by' => (int) $record['submitted_by'],
            'notes' => $record['notes'] ?? null,
            'created_at' => gmdate('Y-m-d H:i:s'),
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ];
        if ($this->wpdb->insert($this->table('mechanic_verifications'), $payload, ['%d','%s','%s','%d','%s','%s','%s']) === false) {
            throw new \RuntimeException('Verification could not be saved.');
        }
        return (int) $this->wpdb->insert_id;
    }

    public function findVerification(int $verificationId): ?array
    {
        $row = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$this->table('mechanic_verifications')} WHERE id = %d LIMIT 1", $verificationId), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function updateVerification(int $verificationId, string $status, int $reviewedBy): bool
    {
        return $this->wpdb->update(
            $this->table('mechanic_verifications'),
            ['status' => $status, 'reviewed_by' => $reviewedBy, 'verified_at' => $status === 'approved' ? gmdate('Y-m-d H:i:s') : null, 'updated_at' => gmdate('Y-m-d H:i:s')],
            ['id' => $verificationId],
            ['%s', '%d', '%s', '%s'],
            ['%d'],
        ) !== false;
    }

    public function saveGalleryItem(int $mechanicId, array $record): int
    {
        $payload = [
            'mechanic_id' => $mechanicId,
            'media_id' => (int) $record['media_id'],
            'category' => $record['category'] ?? null,
            'caption' => $record['caption'] ?? null,
            'sort_order' => (int) ($record['sort_order'] ?? 0),
            'is_cover' => (int) ($record['is_cover'] ?? 0),
            'created_at' => gmdate('Y-m-d H:i:s'),
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ];
        if ($this->wpdb->insert($this->table('mechanic_gallery'), $payload, ['%d','%d','%s','%s','%d','%d','%s','%s']) === false) {
            throw new \RuntimeException('Gallery item could not be saved.');
        }
        return (int) $this->wpdb->insert_id;
    }

    private function table(string $name): string
    {
        if (!isset($this->wpdb->prefix)) {
            throw new \RuntimeException('WordPress database prefix is unavailable.');
        }
        return $this->wpdb->prefix . 'my_' . $name;
    }
}
