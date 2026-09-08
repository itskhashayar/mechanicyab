# Stage 5 — Search & Map

## Architecture self-review

Search is downstream of Locations, Services, Vehicles and Mechanics. It uses a `SearchProvider` contract so the initial MySQL implementation can later be replaced by a dedicated provider without changing the consumer contract. Map is represented by a provider-neutral `MapAdapter`; the current directions adapter produces a reversible external URL and does not introduce SDK credentials or a hard commercial dependency.

## Implemented scope

The Stage includes `SearchRequest`, `SearchProvider`, `MysqlSearchProvider`, `SearchService`, `OrganicRanking`, `MapAdapter` and `OpenDirectionsAdapter`. Search supports text, location, service, vehicle, rating, verification, price, geographic radius and open-now filters. Queries use the runtime WordPress prefix, prepared parameters and bounded pagination. Results are explicitly marked organic; no sponsored or advertising data is joined into the organic path.

The public REST contracts are:

```text
GET /wp-json/mechanicyab/v1/search
GET /wp-json/mechanicyab/v1/map/directions
```

## Security and data boundaries

Search and Map are public read paths. The Search Provider only reads public Mechanics records (`active`, `published`, non-deleted) and returns a constrained field set. It does not expose owner identity. Provider and adapter boundaries contain future integrations; no API key, SDK or paid provider is introduced.

Organic score is an abstraction over relevance, summary quality and profile completeness. It is not a replacement Source of Truth for Reviews, and sponsored visibility is intentionally absent until the Monetization stage.

## Verification

Local Composer validation, PHP syntax checks and PHPUnit tests pass. Failure coverage includes bounded pagination and invalid geographic coordinates. L2 WordPress Runtime, L3 database query execution, query profiling and provider runtime behavior remain `Not Verified` until a real staging environment is available.

## Recovery / disable path

The Search and Map endpoints can be disabled through the module/route registration boundary without deleting Mechanics data. The MySQL provider is replaceable behind `SearchProvider`; the directions adapter is replaceable behind `MapAdapter`.
