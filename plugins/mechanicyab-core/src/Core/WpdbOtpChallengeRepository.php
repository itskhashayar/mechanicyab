<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\OtpChallengeRepository;

final class WpdbOtpChallengeRepository implements OtpChallengeRepository
{
    public function __construct(private readonly object $wpdb) {}

    public function active(string $mobileHash, string $purpose): ?array
    {
        $row = $this->wpdb->get_row($this->wpdb->prepare("SELECT id, otp_hash, attempts, max_attempts, expires_at, status FROM {$this->table()} WHERE mobile_hash = %s AND purpose = %s AND status = 'active' ORDER BY id DESC LIMIT 1", $mobileHash, $purpose), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public function create(string $mobileHash, string $otpHash, string $purpose, string $expiresAt, string $lastSentAt): int
    {
        if ($this->wpdb->insert($this->table(), ['mobile_hash' => $mobileHash, 'otp_hash' => $otpHash, 'purpose' => $purpose, 'expires_at' => $expiresAt, 'last_sent_at' => $lastSentAt, 'created_at' => $lastSentAt], ['%s','%s','%s','%s','%s','%s']) === false) { throw new \RuntimeException('OTP challenge could not be created.'); }
        return (int) $this->wpdb->insert_id;
    }

    public function markSent(int $id, ?string $reference): void
    {
        $this->wpdb->update($this->table(), ['provider_reference' => $reference], ['id' => $id], ['%s'], ['%d']);
    }

    public function consume(int $id): void
    {
        $this->wpdb->query($this->wpdb->prepare("UPDATE {$this->table()} SET status = 'consumed', consumed_at = %s WHERE id = %d AND status = 'active'", gmdate('Y-m-d H:i:s'), $id));
    }

    public function failAttempt(int $id): int
    {
        $this->wpdb->query($this->wpdb->prepare("UPDATE {$this->table()} SET attempts = attempts + 1, status = IF(attempts + 1 >= max_attempts, 'locked', status) WHERE id = %d AND status = 'active'", $id));
        return (int) $this->wpdb->get_var($this->wpdb->prepare("SELECT attempts FROM {$this->table()} WHERE id = %d", $id));
    }

    private function table(): string
    {
        if (!isset($this->wpdb->prefix)) { throw new \RuntimeException('WordPress database prefix is unavailable.'); }
        return $this->wpdb->prefix . 'my_otp_challenges';
    }
}
