<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Core\AuctionEligibility;
use MechanicYab\Core\Core\AnalyticsAggregationService;
use MechanicYab\Core\Core\MechanicPublicResource;
use PHPUnit\Framework\TestCase;

final class FeatureAuditFixTest extends TestCase
{
    public function testAuctionEligibilityUsesExactApprovedRule(): void { $policy=new AuctionEligibility(); $base=['approved_review_count'=>5,'average_rating'=>4.51,'status'=>'active','publication_status'=>'published']; self::assertTrue($policy->eligible($base)); $base['average_rating']=4.5; self::assertFalse($policy->eligible($base)); $base['average_rating']=4.9; $base['approved_review_count']=4; self::assertFalse($policy->eligible($base)); }
    public function testAnalyticsAggregatesEventsAndCtasWithinWindow(): void { $result=(new AnalyticsAggregationService())->aggregate([['event_name'=>'mechanic.call.click','cta_id'=>'CTA_CONTACT_MECHANIC','occurred_at'=>'2026-09-09 10:00:00','outcome'=>'success'],['event_name'=>'mechanic.call.click','cta_id'=>'CTA_CONTACT_MECHANIC','occurred_at'=>'2026-09-10 10:00:00']], '2026-09-09 00:00:00','2026-09-09 23:59:59'); self::assertSame(1,$result['events.mechanic.call.click']); self::assertSame(1,$result['cta.CTA_CONTACT_MECHANIC']); self::assertSame(1,$result['events.mechanic.call.click.success']); }
    public function testPublicResourceIncludesProfileSectionsButNotOwnerIdentity(): void { $resource=(new MechanicPublicResource())->toResponse(['id'=>3,'name'=>'Garage','services'=>[['id'=>2]],'hours'=>[['day'=>1]],'wp_user_id'=>99,'mobile_private'=>'x']); self::assertSame(2,$resource['services'][0]['id']); self::assertArrayNotHasKey('wp_user_id',$resource); self::assertArrayNotHasKey('mobile_private',$resource); }
}
