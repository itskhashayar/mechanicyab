<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Core\LocationTree;
use MechanicYab\Core\Core\ReferenceImport;
use MechanicYab\Core\Core\ServiceCatalog;
use MechanicYab\Core\Core\SlugValidator;
use MechanicYab\Core\Core\VehicleCatalog;
use PHPUnit\Framework\TestCase;

final class ReferenceCatalogTest extends TestCase
{
    public function testLocationTreeBuildsNestedRoots(): void
    {
        $tree = (new LocationTree())->build([
            ['id' => 1, 'parent_id' => null, 'name' => 'Tehran', 'slug' => 'tehran'],
            ['id' => 2, 'parent_id' => 1, 'name' => 'District 1', 'slug' => 'district-1'],
        ]);
        self::assertCount(1, $tree);
        self::assertSame(2, $tree[0]['children'][0]['id']);
    }

    public function testCatalogsNormalizeAndRejectInvalidParents(): void
    {
        self::assertSame('oil-change', (new ServiceCatalog())->normalize(['name' => 'Oil Change', 'slug' => 'oil-change', 'category_id' => 2])['slug']);
        self::assertSame('toyota', (new VehicleCatalog())->normalize(['name' => 'Toyota', 'slug' => 'toyota'], 'brand')['slug']);
        $this->expectException(\InvalidArgumentException::class);
        (new VehicleCatalog())->normalize(['name' => 'Corolla', 'slug' => 'corolla'], 'model');
    }

    public function testImportValidationDetectsDuplicatesAndBadSlugs(): void
    {
        $result = (new ReferenceImport())->validate([
            ['name' => 'Oil Change', 'slug' => 'oil-change'],
            ['name' => 'Duplicate', 'slug' => 'oil-change'],
            ['name' => 'Bad', 'slug' => 'Bad Slug'],
        ], 'service');
        self::assertCount(1, $result['valid']);
        self::assertCount(2, $result['errors']);
    }

    public function testSlugValidatorIsStrictAndStable(): void
    {
        self::assertSame('tehran', (new SlugValidator())->validate('tehran'));
        $this->expectException(\InvalidArgumentException::class);
        (new SlugValidator())->validate('تهران');
    }
}
