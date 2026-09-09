<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Contracts\OtpChallengeRepository;
use MechanicYab\Core\Contracts\SmsProvider;
use MechanicYab\Core\Contracts\UserDataRepository;
use MechanicYab\Core\Core\OtpService;
use MechanicYab\Core\Core\UserAccountService;
use PHPUnit\Framework\TestCase;

final class Stage8SecurityTest extends TestCase
{
    public function testMobileNormalizationAndOtpHashingBoundary(): void
    {
        $repo = new FakeOtpRepository();
        $sms = new FakeSmsProvider();
        $service = new OtpService($repo, $sms);
        $this->assertSame('+989121234567', $service->normalizeMobile('09121234567'));
        $service->request('09121234567');
        $this->assertNotSame('123456', $repo->otpHash);
        $this->assertSame(1, $sms->sentCount);
    }

    public function testInvalidOtpConsumesAnAttemptAndCorrectOtpIsSingleUse(): void
    {
        $repo = new FakeOtpRepository();
        $sms = new FakeSmsProvider();
        $service = new OtpService($repo, $sms);
        $service->request('+989121234567');
        $this->assertFalse($service->verify('+989121234567', '000000'));
        $this->assertSame(1, $repo->attempts);
        $this->assertTrue($service->verify('+989121234567', $sms->code));
        $this->assertSame('consumed', $repo->status);
    }

    public function testAccountServiceRejectsUnsupportedFavoriteEntity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new UserAccountService(new FakeUserRepository()))->addFavorite(7, 'payment', 1);
    }
}

final class FakeSmsProvider implements SmsProvider
{
    public int $sentCount = 0;
    public string $code = '';
    public function sendOtp(string $mobile, string $code): array { $this->sentCount++; $this->code = $code; return ['accepted' => true, 'reference' => 'ref-1', 'code' => null]; }
}

final class FakeOtpRepository implements OtpChallengeRepository
{
    public string $otpHash = '';
    public int $attempts = 0;
    public string $status = 'active';
    public bool $created = false;
    public function active(string $mobileHash, string $purpose): ?array { if (!$this->created || ($this->status !== 'active' && $this->status !== 'locked')) { return null; } return ['id'=>1,'otp_hash'=>$this->otpHash,'attempts'=>$this->attempts,'max_attempts'=>5,'expires_at'=>gmdate('Y-m-d H:i:s', time()+120),'status'=>$this->status]; }
    public function create(string $mobileHash, string $otpHash, string $purpose, string $expiresAt, string $lastSentAt): int { $this->otpHash = $otpHash; $this->created = true; return 1; }
    public function markSent(int $id, ?string $reference): void {}
    public function consume(int $id): void { $this->status = 'consumed'; }
    public function failAttempt(int $id): int { $this->attempts++; return $this->attempts; }
}

final class FakeUserRepository implements UserDataRepository
{
    public function addFavorite(int $userId, string $entityType, int $entityId): bool { return true; }
    public function removeFavorite(int $userId, string $entityType, int $entityId): bool { return true; }
    public function favorites(int $userId): array { return []; }
    public function createVehicle(int $userId, array $data): int { return 1; }
    public function createReminder(int $userId, array $data): int { return 1; }
    public function notify(int $userId, string $type, string $title, string $body, array $data = []): int { return 1; }
}
