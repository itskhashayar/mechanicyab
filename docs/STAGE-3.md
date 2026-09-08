# Stage 3 — Locations, Services and Reference Catalogs

## Scope

Stage 3 adds only the reference domains required before Mechanics and Search: hierarchical Locations, Service Categories, Services, Vehicle Brands, Vehicle Models and Vehicle Trims. Tables are owned by their future modules and use the runtime WordPress prefix. The SchemaManager version advances to `2` and remains additive. Reference Catalog contracts, a Location Tree builder, Service and Vehicle normalizers, strict slug validation, batch import validation and duplicate detection are included.

## Tables

The migration defines `my_locations`, `my_service_categories`, `my_services`, `my_vehicle_brands`, `my_vehicle_models` and `my_vehicle_trims`. Slug uniqueness follows the source schema: location uniqueness is scoped by type, service/category and brand slugs are globally unique, and model/trim slugs are unique within their parent.

## Explicit exclusions

This Stage does not create Mechanics, Mechanic Services, Pricing, Reviews, Search, Map, public routes, CPTs, Post Meta Domain storage, providers, external-source seed data or production imports. The current import component validates and normalizes a batch; it does not commit untrusted external data or pretend to be a production import workflow.

## Verification status

Local PHP syntax, Composer validation and PHPUnit tests pass. Tests cover canonical identity mapping, runtime-prefix mapping, migration planning, location tree construction, catalog normalization, slug validation and import duplicate detection. Real WordPress/MySQL migration (L3) is not claimed until a real runtime exists.

## Rationale

Locations, Services and Vehicles are upstream reference domains for Mechanic and Search. Implementing them before Mechanics keeps the dependency graph acyclic and avoids embedding reference data in later feature tables.
