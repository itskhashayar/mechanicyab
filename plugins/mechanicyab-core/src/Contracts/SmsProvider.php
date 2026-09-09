<?php

declare(strict_types=1);

namespace MechanicYab\Core\Contracts;

interface SmsProvider
{
    /** @return array{accepted: bool, reference: string|null, code: string|null} */
    public function sendOtp(string $mobile, string $code): array;
}

interface OtpChallengeRepository
{
    /** @return array{id: int, otp_hash: string, attempts: int, max_attempts: int, expires_at: string, status: string}|null */
    public function active(string $mobileHash, string $purpose): ?array;
    public function create(string $mobileHash, string $otpHash, string $purpose, string $expiresAt, string $lastSentAt): int;
    public function markSent(int $id, ?string $reference): void;
    public function consume(int $id): void;
    public function failAttempt(int $id): int;
}
