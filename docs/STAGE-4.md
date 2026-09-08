# Stage 4 — Mechanics Domain

## Architecture self-review

The Mechanics module is downstream of the canonical WordPress identity bridge, Locations and Services. `wp_users.ID` remains the owner identity key. Mechanic data is stored in Custom Tables owned by the Mechanics module; it is not a CPT, Post Meta record or Theme concern. Public output is represented through a sanitized profile view model.

## Implemented scope

The schema version advances to `3` and adds runtime-prefixed tables for mechanics, mechanic locations, business profiles, verifications, working hours, special hours, mechanic services, service-vehicle applicability and mechanic prices. The `MechanicProfile` contract validates required ownership/location/phone fields and exposes a public serializer that excludes owner identity and private fields.

## Guardrails

Organic ranking, Reviews, Ads, Auction, Payments, Search, Map and Analytics are not implemented in this Stage. `average_rating` and `review_count` remain summary fields and are not treated as an independent trust source. Verification is represented as a lifecycle table only; no moderation workflow or eligibility rule is duplicated here.

## Verification

Local PHP syntax, Composer validation and PHPUnit coverage are required before commit. Runtime WordPress/MySQL migration remains a separate L3 track and is not claimed without evidence.
