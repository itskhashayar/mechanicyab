<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Core\SchemaManager;
use PHPUnit\Framework\TestCase;

final class SchemaManagerTest extends TestCase
{
    public function testTableNamesUseRuntimePrefixAndCoreSchemaSet(): void
    {
        $schema = new SchemaManager();
        $tables = $schema->tableNames('custom_');
        self::assertSame('custom_my_users', $tables['users']);
        self::assertSame('custom_my_module_states', $tables['module_states']);
        self::assertSame('custom_my_locations', $tables['locations']);
        self::assertSame('custom_my_vehicle_trims', $tables['vehicle_trims']);
        self::assertCount(23, $tables);
    }

    public function testPendingMigrationIsReportedWithoutWordPressRuntime(): void
    {
        $schema = new SchemaManager();
        self::assertSame(['stage-2-core-schema-v1', 'stage-3-reference-schema-v2', 'stage-4-mechanics-schema-v3'], $schema->pendingMigrations());
    }

    public function testWordPressIsCanonicalIdentityAndMyUsersIsOnlyAnExtension(): void
    {
        $schema = new SchemaManager();
        self::assertSame([
            'canonical_table' => 'wp_users',
            'canonical_key' => 'ID',
            'profile_table' => 'my_users',
            'profile_key' => 'wp_user_id',
            'role_link_key' => 'wp_user_id',
            'preference_key' => 'wp_user_id',
        ], $schema->identityContract());
    }
}
