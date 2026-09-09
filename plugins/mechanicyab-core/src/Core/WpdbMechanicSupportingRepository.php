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

    public function saveProfileCollection(string $collection, int $mechanicId, array $record): int
    {
        $definitions = [
            'special_hours' => ['table' => 'mechanic_special_hours', 'fields' => ['date','is_closed','open_time','close_time','reason']],
            'employees' => ['table' => 'mechanic_employees', 'fields' => ['name','position','specialty','experience_years','bio','status','sort_order']],
            'social_profiles' => ['table' => 'mechanic_social_profiles', 'fields' => ['platform','url','username','status']],
            'offers' => ['table' => 'mechanic_offers', 'fields' => ['title','description','discount_type','discount_value','starts_at','ends_at','status']],
            'faq' => ['table' => 'mechanic_faqs', 'fields' => ['question','answer','status','sort_order']],
        ];
        if (!isset($definitions[$collection])) { throw new \InvalidArgumentException('Unsupported profile collection.'); }
        $definition = $definitions[$collection]; $payload = ['mechanic_id' => $mechanicId]; $formats = ['%d'];
        foreach ($definition['fields'] as $field) { if (array_key_exists($field, $record)) { $payload[$field] = $record[$field]; $formats[] = is_int($record[$field]) ? '%d' : '%s'; } }
        $payload['created_at'] = gmdate('Y-m-d H:i:s'); $payload['updated_at'] = $payload['created_at']; $formats[]='%s'; $formats[]='%s';
        if ($this->wpdb->insert($this->table($definition['table']), $payload, $formats) === false) { throw new \RuntimeException('Profile collection record could not be saved.'); }
        return (int) $this->wpdb->insert_id;
    }

    public function listProfileCollection(string $collection, int $mechanicId): array
    {
        $tables = ['special_hours' => 'mechanic_special_hours', 'employees' => 'mechanic_employees', 'social_profiles' => 'mechanic_social_profiles', 'offers' => 'mechanic_offers', 'faq' => 'mechanic_faqs'];
        if (!isset($tables[$collection])) { throw new \InvalidArgumentException('Unsupported profile collection.'); }
        $rows = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM {$this->table($tables[$collection])} WHERE mechanic_id = %d ORDER BY id DESC", $mechanicId), ARRAY_A);
        return is_array($rows) ? array_map('array_filter', $rows) : [];
    }

    public function deleteProfileCollection(string $collection, int $mechanicId, int $recordId): bool
    {
        $tables = ['special_hours' => 'mechanic_special_hours', 'employees' => 'mechanic_employees', 'social_profiles' => 'mechanic_social_profiles', 'offers' => 'mechanic_offers', 'faq' => 'mechanic_faqs'];
        if (!isset($tables[$collection])) { throw new \InvalidArgumentException('Unsupported profile collection.'); }
        return $this->wpdb->delete($this->table($tables[$collection]), ['id' => $recordId, 'mechanic_id' => $mechanicId], ['%d','%d']) !== false;
    }

    private function table(string $name): string
    {
        if (!isset($this->wpdb->prefix)) {
            throw new \RuntimeException('WordPress database prefix is unavailable.');
        }
        return $this->wpdb->prefix . 'my_' . $name;
    }
}
