# Stage 3 — Locations, Services and Reference Catalogs

## Scope

Stage 3 adds only the reference domains required before Mechanics and Search: hierarchical Locations, Service Categories, Services, Vehicle Brands, Vehicle Models and Vehicle Trims. Tables are owned by their future modules and use the runtime WordPress prefix. The SchemaManager version advances to `2` and remains additive.

## Tables

The migration defines `my_locations`, `my_service_categories`, `my_services`, `my_vehicle_brands`, `my_vehicle_models` and `my_vehicle_trims`. Slug uniqueness follows the source schema: location uniqueness is scoped by type, service/category and brand slugs are globally unique, and model/trim slugs are unique within their parent.

## Explicit exclusions

This Stage does not create Mechanics, Mechanic Services, Pricing, Reviews, Search, Map, public routes, CPTs, Post Meta Domain storage, providers, seed data from external sources or production imports. The migration only creates schema; reference data import/seed will be added with its own validation and resumable workflow.

## Verification status

Local PHP syntax, Composer validation and PHPUnit tests pass. Tests cover runtime-prefix mapping and migration planning. Real WordPress/MySQL migration (L3) is not claimed until a real runtime exists.

## Rationale

Locations, Services and Vehicles are upstream reference domains for Mechanic and Search. Implementing them before Mechanics keeps the dependency graph acyclic and avoids embedding reference data in later feature tables.
