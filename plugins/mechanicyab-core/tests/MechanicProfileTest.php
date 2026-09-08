<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Core\MechanicProfile;
use MechanicYab\Core\Core\SchemaManager;
use PHPUnit\Framework\TestCase;

final class MechanicProfileTest extends TestCase
{
    public function testMechanicsSchemaUsesRuntimePrefixAndExpectedTables(): void
    {
        $tables = (new SchemaManager())->tableNames('tenant_');
        self::assertSame('tenant_my_mechanics', $tables['mechanics']);
        self::assertSame('tenant_my_mechanic_services', $tables['mechanic_services']);
        self::assertSame('tenant_my_mechanic_prices', $tables['mechanic_prices']);
    }

    public function testProfileNormalizationRequiresCanonicalOwnerAndLocation(): void
    {
        $profile = new MechanicProfile();
        $normalized = $profile->normalize([
            'name' => 'Mechanic One',
            'slug' => 'mechanic-one',
            'owner_user_id' => 12,
            'location_id' => 8,
            'phone' => '02100000000',
        ]);
        self::assertSame(12, $normalized['owner_user_id']);
        self::assertSame('draft', $normalized['publication_status']);
    }

    public function testPublicViewDoesNotExposeOwnerOrPrivateFields(): void
    {
        $view = (new MechanicProfile())->publicView([
            'id' => 5,
            'name' => 'Mechanic One',
            'slug' => 'mechanic-one',
            'owner_user_id' => 99,
            'phone' => '02100000000',
            'location_id' => 8,
        ]);
        self::assertArrayNotHasKey('owner_user_id', $view);
        self::assertSame(5, $view['id']);
    }
}
