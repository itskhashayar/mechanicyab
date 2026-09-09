# Stage 9 — Analytics Foundation & Reporting Read Models

## Architecture self-review

Analytics is downstream of Product events and does not replace any Domain Source of Truth. The boundary is:

```text
UI/API action → AnalyticsService → AnalyticsRepository → raw event table → aggregate read models
```

Only allowlisted context fields are accepted. Session identifiers are HMAC-hashed, server timestamps are used, and raw phone numbers, OTPs, payment payloads and arbitrary client payloads are rejected by omission.

## Implemented scope

Schema version `7` adds `analytics_events`, `analytics_hourly_metrics` and `analytics_daily_metrics`. The Core Plugin adds the locked CTA registry, event naming validation, idempotency/dedupe key generation, privacy-safe context extraction, a prepared repository and a public ingestion endpoint.

Locked CTA IDs include the twelve identifiers specified in the analytics source document. Organic, advertising, financial and AI metrics are not mixed into this raw contract; their domain-specific events will be added by their owning Stages.

## Verification

Composer validation, PHP syntax checks and PHPUnit pass. Current local result: 37 tests and 72 assertions. Analytics ingestion, real aggregation jobs, bot classification, query performance and L2/L3 database behavior are `Not Verified` until staging evidence exists.

## Privacy and retention

No raw mobile, OTP, payment credential or arbitrary payload is persisted by the AnalyticsService. Retention duration, deletion workflow and legal privacy policy remain a Founder/Privacy decision and are not invented in this Stage.

## Recovery

The ingestion REST route can be disabled independently. Raw events remain append-oriented; aggregate read models can be rebuilt from raw events when a real scheduler/worker exists. Duplicate events are treated idempotently through the unique dedupe key.
