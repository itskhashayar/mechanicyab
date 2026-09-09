<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Core\SeoService;
use PHPUnit\Framework\TestCase;

final class SeoServiceTest extends TestCase
{
    public function testCanonicalAndSchemaAreStable(): void { $seo=new SeoService(); self::assertSame('https://example.test/mechanic/a/',$seo->canonical('https://example.test/mechanic/a')); self::assertSame('LocalBusiness',$seo->schema('LocalBusiness',['name'=>'A','url'=>'https://example.test/mechanic/a'])['@type']); }
    public function testSitemapChunkIsBounded(): void { $urls=array_fill(0,50001,'https://example.test/a'); self::assertSame(50000,substr_count((new SeoService())->sitemapChunk($urls),'<url>')); }
}
