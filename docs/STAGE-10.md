# Stage 10 — Monetization & Financial Integrity Foundation

## Architecture self-review

Payment is isolated behind `PaymentGateway`; the application depends on the contract rather than a provider SDK. Zibal is the Primary provider according to Founder instruction. Zarinpal and IDPay are replaceable adapters. No auction, advertising allocation, subscription billing rule or refund execution policy is invented beyond the approved financial foundation.

## Implemented scope

Schema versions `8` and `9` add payments, payment transactions, refunds and immutable ledger entries. The local payment record owns the immutable order key, payer, amount, currency, gateway and idempotency key. The payment service refuses unauthenticated payers, rejects invalid amounts, prevents re-creating an existing order and verifies against the server-stored amount and gateway reference. Ledger postings require at least two entries with equal debit and credit totals and cannot be edited through the Ledger contract.

The callback endpoint is intentionally untrusted. It only identifies the local payment and triggers server-side verification. Fulfillment/finalization occurs only after the gateway adapter reports verified success. Zibal result `100` and duplicate result `201` are handled as success/duplicate-safe outcomes. Zarinpal `100/101` and IDPay `100/101/200` are represented in their adapters.

## Security and integrity rules

Gateway credentials are read from environment configuration and never persisted in code. Amounts are integer IRR values at the request boundary, and the browser/callback cannot select the authoritative amount. Local order keys and idempotency keys are unique. Finalization requires the payment to be in a pending/requested state and matches the stored amount.

## Verification

Composer validation, PHP syntax checks and PHPUnit pass locally: 42 tests and 78 assertions. Provider sandbox calls, real callback behavior, MySQL transaction isolation and refund execution are `Not Verified` without staging and approved provider credentials. No `Runtime Verified` claim is made.

## Founder decisions recorded

The Founder explicitly approved proceeding with Payment Architecture and selected Zibal as Primary, with Zarinpal and IDPay as fallback adapters. Refund semantics remain provider-specific and are not auto-invented where official documentation does not publish a stable endpoint contract.

## Recovery

Payment routes can be disabled independently. Pending payments are retained for inquiry/reconciliation. Code rollback does not delete financial records. Any future refund, auction, subscription or fulfillment rule must preserve ledger invariants and receive the required Founder approval before activation.
