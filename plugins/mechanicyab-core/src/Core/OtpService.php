<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

use MechanicYab\Core\Contracts\OtpChallengeRepository;
use MechanicYab\Core\Contracts\SmsProvider;

final class OtpService
{
    public function __construct(private readonly OtpChallengeRepository $repository, private readonly SmsProvider $provider, private readonly int $ttlSeconds = 120, private readonly int $cooldownSeconds = 60) {}

    public function request(string $mobile, string $purpose = 'login'): int
    {
        $mobile = $this->normalizeMobile($mobile);
        $mobileHash = $this->hash($mobile);
        $active = $this->repository->active($mobileHash, $purpose);
        if ($active !== null && strtotime((string) $active['expires_at']) > time() && strtotime((string) $active['expires_at']) - $this->ttlSeconds + $this->cooldownSeconds > time()) {
            throw new \RuntimeException('OTP cooldown is active.');
        }
        $code = (string) random_int(100000, 999999);
        $now = gmdate('Y-m-d H:i:s');
        $id = $this->repository->create($mobileHash, $this->hash($code), $purpose, gmdate('Y-m-d H:i:s', time() + $this->ttlSeconds), $now);
        try {
            $result = $this->provider->sendOtp($mobile, $code);
            if (!$result['accepted']) { throw new \RuntimeException('SMS was not accepted.'); }
            $this->repository->markSent($id, $result['reference']);
        } catch (\Throwable $exception) {
            throw new \RuntimeException('OTP delivery failed.', 0, $exception);
        }
        return $id;
    }

    public function verify(string $mobile, string $code, string $purpose = 'login'): bool
    {
        $active = $this->repository->active($this->hash($this->normalizeMobile($mobile)), $purpose);
        if ($active === null || $active['status'] !== 'active' || strtotime((string) $active['expires_at']) <= time() || (int) $active['attempts'] >= (int) $active['max_attempts']) {
            return false;
        }
        $valid = hash_equals($this->hash($code), (string) ($active['otp_hash'] ?? ''));
        if (!$valid) {
            $this->repository->failAttempt((int) $active['id']);
            return false;
        }
        $this->repository->consume((int) $active['id']);
        return true;
    }

    public function normalizeMobile(string $mobile): string
    {
        $mobile = preg_replace('/[^0-9+]/', '', trim($mobile)) ?? '';
        if (str_starts_with($mobile, '09')) { $mobile = '+98' . substr($mobile, 1); }
        if (!preg_match('/^\+98[0-9]{10}$/', $mobile)) { throw new \InvalidArgumentException('Invalid Iranian mobile number.'); }
        return $mobile;
    }

    private function hash(string $value): string
    {
        $salt = function_exists('wp_salt') ? wp_salt('auth') : 'mechanicyab-test-salt';
        return hash_hmac('sha256', $value, $salt);
    }
}
