<?php

declare(strict_types=1);

namespace MechanicYab\Core\Tests;

use MechanicYab\Core\Contracts\SearchProvider;
use MechanicYab\Core\Contracts\SearchRequest;
use MechanicYab\Core\Core\OpenDirectionsAdapter;
use MechanicYab\Core\Core\OrganicRanking;
use MechanicYab\Core\Core\PublicRouteResolver;
use MechanicYab\Core\Core\SearchService;
use MechanicYab\Core\Modules\SearchModule;
use PHPUnit\Framework\TestCase;

final class SearchMapTest extends TestCase
{
    public function testSearchRequestPaginationIsBounded(): void
    {
        $request = new SearchRequest(['location_id' => 4], 2, 10, 'brake');
        self::assertSame(10, $request->offset());
        self::assertSame(10, $request->perPage);
    }

    public function testSearchServiceKeepsProviderBoundary(): void
    {
        $service = new SearchService(new FakeSearchProvider());
        $result = $service->search('brake', ['location_id' => 4], 1, 5);
        self::assertSame('brake', $result['items'][0]['query']);
        self::assertTrue($result['meta']['organic']);
    }

    public function testOrganicRankingIsSeparateFromSponsoredData(): void
    {
        $row = (new OrganicRanking())->score(['name' => 'Brake Center', 'average_rating' => 4, 'review_count' => 20, 'profile_completion_percent' => 80], 'brake');
        self::assertArrayHasKey('organic_score', $row);
        self::assertArrayNotHasKey('sponsored_rank', $row);
    }

    public function testDirectionsAdapterReturnsProviderContract(): void
    {
        $result = (new OpenDirectionsAdapter())->directions(35.7, 51.3, 35.8, 51.4);
        self::assertSame('openstreetmap', $result['provider']);
        self::assertStringContainsString('from=', $result['url']);
    }

    public function testDirectionsRejectsOutOfBoundsCoordinates(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new OpenDirectionsAdapter())->directions(95, 51, 35, 51);
    }

    public function testSearchModuleMetadataIsValid(): void
    {
        $metadata = (new SearchModule())->metadata();
        $metadata->validate();
        self::assertSame('search', $metadata->id);
        self::assertSame('degraded', $metadata->failurePolicy);
    }

    public function testPublicRoutesStayInCoreResolverBoundary(): void
    {
        self::assertSame(['search.php', 'map.php', 'single-mechanic.php'], array_values((new PublicRouteResolver())->supportedRoutes()));
    }
}

final class FakeSearchProvider implements SearchProvider
{
    public function search(SearchRequest $request): array
    {
        return ['items' => [['query' => $request->query]], 'meta' => ['organic' => true]];
    }
}
