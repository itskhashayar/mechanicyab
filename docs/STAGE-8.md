# Stage 8 — Authentication, User Retention & Notifications

## Architecture decision

`wp_users.ID` remains the canonical identity. No parallel identity table is created. The user profile table remains `my_users` and stores only the verified mobile mapping and profile state.

OTP is generated and verified by the application. Only an HMAC hash is stored in `my_otp_challenges`; raw OTP values are never persisted. Challenges have a short TTL, maximum attempts, cooldown, status transitions and single-use consumption. WordPress secure auth cookies and WordPress session revocation are used after successful verification.

The SMS boundary is:

```text
OtpService → SmsProvider → Provider Adapter → SMS Provider
```

Primary selection: **Kavenegar Verify/Lookup**, because official documentation exposes a dedicated OTP/Lookup endpoint, template support, message references and explicit response status. IPPanel Edge Pattern and SMS.ir Verify are implemented as replaceable adapters. No credentials are stored in the repository; runtime environment variables are required.

Official references reviewed:

- [Kavenegar REST API](https://kavenegar.com/rest.html)
- [Kavenegar Verification](https://kavenegar.com/sms/verification)
- [IPPanel Edge Documentation](https://ippanelcom.github.io/Edge-Document/fa/docs/)
- [IPPanel Pattern API](https://ippanelcom.github.io/Edge-Document/docs/send/pattern)
- [SMS.ir REST API](https://sms.ir/rest-api/)
- [SMS.ir OTP](https://sms.ir/feature/%D9%BE%DB%8C%D8%A7%D9%85%DA%A9-otp/)

## Implemented scope

Stage 8 adds schema version `6` for OTP challenges, Favorites, User Vehicles, Vehicle Service Records, Vehicle Reminders, Notifications and Notification Deliveries. It adds Auth/OTP services, provider adapters, user-owned data services and prepared persistence boundaries.

Implemented REST boundaries include OTP request/verify/logout, Favorites, Vehicles, Service History, Reminders and Notifications. Authenticated account actions require WordPress login. Favorite ownership, vehicle ownership and supported entity types are enforced in the application service.

## Threat model and controls

| Threat | Control |
|---|---|
| OTP database disclosure | HMAC hash only; no plaintext OTP storage |
| OTP replay | Active challenge is consumed on success; status is single-use |
| Brute force | Attempt counter and lock threshold |
| SMS abuse | Cooldown, bounded TTL and provider boundary |
| Provider credential leakage | Environment-only credentials; no secrets in Git or responses |
| Session theft/reuse | WordPress auth cookie and WordPress session destruction on logout |
| Cross-user data access | Account services require authenticated canonical user ID; repositories accept explicit owner identity |
| Private identity exposure | Public resources do not expose mobile or WordPress identity |
| Provider outage | Adapter exceptions are contained; domain is not coupled to SDKs |
| Accidental duplicate favorite | Unique `(wp_user_id, entity_type, entity_id)` constraint |

## Explicit limitations

SMS delivery, real WordPress cookie behavior, real MySQL migrations, provider sandbox calls, IP rate limiting at edge infrastructure, account recovery UX and notification delivery workers are `Not Verified` without staging. Application cooldown and attempts are implemented; production deployment still requires infrastructure-level rate limiting and monitoring.

Payment remains outside Stage 8 and is not implemented here.

## Verification

Local Composer validation, PHP syntax checks, contract tests, security tests and forbidden-scope scans are required. L2/L3 Runtime Verified status is not claimed until evidence is available.

## Recovery

OTP and account REST routes can be disabled without deleting canonical WordPress users. Schema rollback is non-destructive: revert code and preserve Stage 8 tables for recovery. Provider selection is configuration/adapter based and can be switched without changing `OtpService`.
