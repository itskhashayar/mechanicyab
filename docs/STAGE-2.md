# Stage 2 — Schema & Identity Foundation

## Scope

Stage 2 introduces only the persistence foundation required by later Domains. It contains a WordPress-aware `SchemaManager`, runtime-prefix table naming, version `1` migration orchestration through `dbDelta()` as a low-level helper, pending migration reporting, schema health reporting, and the initial identity/permission/module tables defined by File 07 and File 12.

The tables created by this Stage are `my_users`, `my_roles`, `my_permissions`, `my_role_user`, `my_permission_role`, `my_user_preferences`, `my_module_settings`, and `my_module_states`, always prefixed through `$wpdb->prefix`. No `wp_` prefix is hard-coded in the migration path.

## Explicit exclusions

This Stage does not implement OTP, SMS, User login flow, Mechanics, Locations, Vehicles, Services, Reviews, Search, Map, Analytics, Payments, Ads, Auction, AI, SEO or any production provider. The `users` table is a domain profile bridge keyed to `wp_users`; it does not replace WordPress identity.

## Migration safety

The Stage 2 migration is additive and versioned. It does not drop tables, reset a database, delete data or perform destructive alteration. Activation calls the schema manager, while the diagnostic CLI exposes `wp mechanicyab migrate`. The migration requires a real WordPress database runtime; if no `$wpdb` is available, it fails explicitly rather than creating a mock.

## Verification

Local checks pass with PHP 8.3.6, Composer 2.7.1 and PHPUnit 10.5.64. The suite now contains six tests and eight assertions, including runtime-prefix table mapping and pending migration reporting. L0/L1 are verified locally. L3 (real database migration and seed) is not claimed until a real WordPress/MySQL runtime is available.

## Rationale

The schema is intentionally limited to the Stage 2 dependency foundation. Domain table creation remains staged so later modules can own their tables and migrations without pulling Product Features ahead of their dependency order.
