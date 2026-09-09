# Stage 1 — WordPress Foundation

## Status

Implementation started on branch `feature/stage-1-foundation` from the Greenfield repository. File 12 is the implementation authority.

## Scope implemented

- Core Plugin bootstrap and lifecycle boundary.
- Presentation-only Theme skeleton with soft Plugin dependency.
- PSR-4 Composer autoload declaration with safe development fallback.
- Module metadata contract and registry.
- Duplicate module ID, missing dependency, invalid metadata and dependency-cycle validation.
- Deterministic module boot and module status reporting.
- Settings, Feature Flag, Capability, Health, Response and diagnostic contracts.
- Admin diagnostic shell, REST v1 diagnostic endpoint and WP-CLI diagnostic namespace.
- Foundation tests, CI syntax/static/test workflow and repository documentation.

## Explicitly not implemented

No Domain Custom Tables, CPT/Post Meta Domain workaround, Mechanics, Reviews, Locations, Vehicles, Services, Search, Map, Ranking, Auction, Ads, Payments, Ledger, OTP, SMS, AI, RAG, Analytics ingestion, SEO programmatic pages, production integrations, credentials or production deployment.

## Verification status

Sandbox inspection found no PHP, WordPress, WP-CLI, Composer or Web Server runtime. Therefore local L1/L2 execution requires the repository CI or an approved PHP/WordPress test environment. No Runtime Verified claim is made until evidence is captured.

## Decisions

- The repository is the official Greenfield workspace.
- The Theme/Plugin split follows File 12.
- The Foundation uses PHP 8.1+ and a PSR-4 namespace rooted at `MechanicYab\\Core\\`.
- Composer is declared as the preferred autoload path; a safe fallback autoloader keeps a clean WordPress checkout bootable before dependencies are installed.
- CI targets PHP 8.2 and runs syntax, Composer validation and foundation tests.

## Next gate

Run CI and an actual WordPress runtime harness. Only after L0/L1/L2 evidence passes can Stage 1 be called Runtime Verified.
