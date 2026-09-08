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
        self::assertCount(8, $tables);
    }

    public function testPendingMigrationIsReportedWithoutWordPressRuntime(): void
    {
        $schema = new SchemaManager();
        self::assertSame(['stage-2-core-schema-v1'], $schema->pendingMigrations());
    }
}
