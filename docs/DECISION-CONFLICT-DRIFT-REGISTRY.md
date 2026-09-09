# Decision, Conflict, and Drift Registry — Release Candidate

| ID | Type | Decision/Observation | Status | Evidence/Action |
|---|---|---|---|---|
| RC-001 | Decision | File 12 remains the implementation authority; Theme is presentation-only and Core owns domain logic. | Active | Confirmed by repository structure and boundary tests |
| RC-002 | Decision | Domain-heavy data remains in runtime-prefixed custom tables; `wp_users.ID` remains canonical identity. | Active | SchemaManager identity contract and tests |
| RC-003 | Decision | Runtime and Production claims are withheld without WordPress/MySQL/provider evidence. | Active | Final RC audit |
| RC-004 | Decision | No new product feature is introduced during RC stabilization. | Active | Stabilization scope |
| RC-005 | Bug fix | Pending migration reporting omitted v11 for stored schema versions 3–9. | Fixed | SchemaManager and regression validation |
| RC-006 | Environment gap | WordPress/MySQL activation, dbDelta execution, REST runtime and browser QA are unavailable in the repository sandbox. | Open, environment-only | Must be verified on staging |
| RC-007 | Environment gap | SMS/payment/AI provider calls require real credentials or sandbox credentials. | Open, environment-only | No secrets committed |
| RC-008 | Risk | Inline service construction remains in the plugin bootstrap. | Accepted for RC | Refactor is deferred because it is not required to resolve a release-blocking defect |
| RC-009 | Risk | CI success for the exact final commit must be observed after GitHub Actions completes. | Pending | Workflow is configured; run status must be checked |

No unresolved architecture, security-core, privacy-boundary, financial-logic, or destructive-database conflict was found during this RC audit.
