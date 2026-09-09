<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Core\AuditService;
use PHPUnit\Framework\TestCase;

final class AuditServiceTest extends TestCase { public function testAuditEventHasServerTimestamp(): void { $event=(new AuditService())->event(1,'review.approve','review',4); self::assertSame(1,$event['actor_id']); self::assertNotEmpty($event['created_at']); } }
