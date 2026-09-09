<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Contracts\AnalyticsRepository;
use MechanicYab\Core\Core\AnalyticsService;
use PHPUnit\Framework\TestCase;

final class AnalyticsServiceTest extends TestCase
{
    public function testCtaRegistryAndSafeContextAreEnforced(): void
    {
        $repo = new FakeAnalyticsRepository();
        $service = new AnalyticsService($repo);
        self::assertTrue($service->track('mechanic.call.click', 'CTA_CONTACT_MECHANIC', ['entity_type'=>'mechanic','entity_id'=>3,'session_id'=>'anonymous-1','raw_phone'=>'must-not-enter']));
        self::assertArrayNotHasKey('raw_phone', $repo->event);
        self::assertArrayHasKey('session_hash', $repo->event);
    }

    public function testUnknownCtaAndInvalidEventAreRejected(): void
    {
        $service = new AnalyticsService(new FakeAnalyticsRepository());
        $this->expectException(\InvalidArgumentException::class);
        $service->track('bad-event', 'CTA_UNKNOWN');
    }

    public function testLockedCtaCountIsStable(): void
    {
        self::assertCount(12, (new AnalyticsService(new FakeAnalyticsRepository()))->ctaIds());
    }
}

final class FakeAnalyticsRepository implements AnalyticsRepository
{
    public array $event = [];
    public function record(array $event): bool { $this->event = $event; return true; }
    public function daily(string $metricKey, string $from, string $to): array { return []; }
}
