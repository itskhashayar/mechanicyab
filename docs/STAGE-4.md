# Stage 4 — Mechanics Domain

## Architecture self-review

The Mechanics module is downstream of the canonical WordPress identity bridge, Locations and Services. `wp_users.ID` remains the owner identity key. Mechanic data is stored in Custom Tables owned by the Mechanics module; it is not a CPT, Post Meta record or Theme concern. Public output is represented through a sanitized profile view model.

## Implemented scope

The schema version advances to `4` and adds runtime-prefixed tables for mechanics, mechanic locations, business profiles, verifications, working hours, special hours, mechanic services, service-vehicle applicability, mechanic prices, media, gallery, employees and social profiles. The `MechanicProfile` contract validates required ownership/location/phone fields and exposes a public serializer that excludes owner identity and private fields.

The completed service layer includes `MechanicRepository`, a prepared-query `WpdbMechanicRepository`, ownership authorization, CRUD application services, supporting persistence for hours/verifications/gallery, verification transition persistence, profile normalization, and public resource serialization. Repository classes do not perform authorization; authorization is enforced in application services. REST errors do not expose internal exception messages.

## Guardrails

Organic ranking, Reviews, Ads, Auction, Payments, Search, Map and Analytics are not implemented in this Stage. `average_rating` and `review_count` remain summary fields and are not treated as an independent trust source. Verification is limited to submission and controlled state transition persistence; no full moderation workflow or eligibility rule is duplicated here.

## Verification

Local PHP syntax, Composer validation, 20 PHPUnit tests and 40 assertions pass before commit. Runtime WordPress/MySQL migration remains a separate L3 track and is not claimed without evidence.
