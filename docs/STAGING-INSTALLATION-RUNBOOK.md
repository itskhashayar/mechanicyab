# MechanicYab Release Candidate — WordPress Staging Runbook

## 1. Purpose and Release Boundary

This runbook installs and verifies the frozen MechanicYab Release Candidate on a disposable or recoverable WordPress staging environment. It does not introduce product scope or activate new features. The target code is branch `feature/stage-1-foundation` at commit `387eda2`.

The Release Candidate is repository-validated and CI-verified. It is not Runtime Verified or Production Ready until the staging checks in this document produce evidence.

## 2. Required Staging Profile

| Component | Required baseline | Verification command or evidence |
|---|---|---|
| PHP | 8.1 or newer; PHP 8.2 is the CI reference | `php -v` |
| WordPress | 6.4 or newer | `wp core version` |
| Database | MySQL 8.0+ preferred, or a compatible MariaDB release supported by the hosting policy | `wp db cli -- -e 'SELECT VERSION();'` |
| Web server | Apache 2.4+ with rewrite support, or Nginx with the equivalent WordPress front-controller configuration | Web-server config review and permalink smoke test |
| HTTPS | Required for staging authentication, REST, callback, and provider testing | `curl -I https://staging.example` and certificate inspection |
| WP-CLI | Current supported release compatible with the installed PHP | `wp --info` |
| Composer | Composer 2.x | `composer --version` |
| PHP extensions | `mysqli` or `pdo_mysql`, `mbstring`, `json`, `openssl`, `curl`, `fileinfo`, `xml`, `zip`, and standard WordPress-required extensions | `php -m` |
| Cron | WP-Cron enabled, or a real system cron invoking `wp cron event run --due` | Cron configuration and execution log |
| Storage | Writable `wp-content/uploads` with backup coverage | Upload test and filesystem permissions review |
| Access | WordPress administrator, database backup/restore access, SSH/WP-CLI access, and web-server log access | Access checklist |

The staging database and uploads must be isolated from production. Use a staging domain and staging-only credentials.

## 3. Obtain and Verify the Release Candidate

Clone the official repository and check out the exact frozen commit:

```bash
git clone https://github.com/itskhashayar/mechanicyab.git
cd mechanicyab
git checkout 387eda2
git status --short
git rev-parse HEAD
```

The expected revision is:

```text
387eda200f6cc3f1781b6629eb9a8c06a550ee71
```

Copy or link these directories into the staging WordPress installation:

```text
plugins/mechanicyab-core   -> wp-content/plugins/mechanicyab-core
theme/mechanicyab-theme    -> wp-content/themes/mechanicyab-theme
```

Do not copy the repository `vendor/` directory from a local development machine unless it was built from this exact commit. Build dependencies in the plugin directory:

```bash
cd wp-content/plugins/mechanicyab-core
composer install --no-dev --prefer-dist --no-interaction
composer validate --strict --no-check-publish
```

For a staging QA build where test tooling is intentionally retained, omit `--no-dev`.

## 4. Configuration Boundary and Secrets

No secret belongs in Git, theme files, database fixtures, logs, or the WordPress options table unless the deployment policy explicitly encrypts and protects it. Inject secrets through the hosting secret manager, process environment, or an equivalent protected configuration boundary.

The frozen plugin currently reads these provider variables:

| Variable | Used for | Required for initial install |
|---|---|---:|
| `MECHANICYAB_KAVENEGAR_API_KEY` | Kavenegar SMS adapter | No; required for real OTP delivery |
| `MECHANICYAB_KAVENEGAR_TEMPLATE` | Kavenegar OTP template | No; required with Kavenegar |
| `MECHANICYAB_ZIBAL_MERCHANT` | Zibal payment adapter | No; required for payment sandbox |

The repository also contains provider-neutral adapter boundaries for IPPanel, SMS.ir, Zarinpal, and IDPay. Their credentials must be added only when the corresponding adapter is deliberately selected and the staging test plan has been approved. Do not place real values in `.env.example`, documentation examples, screenshots, tickets, or test fixtures.

Before enabling a provider, record the provider name, sandbox/production mode, callback URL, secret owner, rotation procedure, and test account in the staging change record.

## 5. Installation Procedure

Create a database backup or fresh staging database before activation. Confirm that the WordPress table prefix is the intended staging prefix.

Activate the plugin and theme:

```bash
wp plugin activate mechanicyab-core
wp theme activate mechanicyab-theme
wp rewrite flush
```

Run the schema migration through the plugin's supported WP-CLI command:

```bash
wp mechanicyab migrate
wp mechanicyab health
```

Expected evidence includes a healthy schema report and version `11`. The migration creates or updates runtime-prefixed `my_` custom tables through WordPress `dbDelta`. It must not modify WordPress core tables beyond the intended WordPress option/capability integration and must not drop domain tables.

Verify the plugin and theme are active:

```bash
wp plugin status mechanicyab-core
wp theme status mechanicyab-theme
wp option get mechanicyab_schema_version
```

If the command is not available in the installed WP-CLI registration, use the WordPress admin activation path and inspect the plugin health endpoint. Do not bypass the migration with manual SQL unless a database incident procedure explicitly requires it.

## 6. Smoke Verification Checklist

Record the result, timestamp, operator, and log reference for every item.

| Area | Check | Expected result |
|---|---|---|
| Home | Open the staging home URL over HTTPS | Theme renders without PHP fatal errors |
| RTL | Inspect Persian layout on mobile and desktop widths | RTL layout and approved tokens render correctly |
| REST | Request `GET /wp-json/mechanicyab/v1/health` | JSON response reports application/schema status |
| Search | Request the public search endpoint with bounded pagination | Valid response; no SQL error |
| Mechanic | Open a public mechanic resource | Public fields only; no owner identity/private fields |
| Auth | Exercise OTP request only with a staging SMS configuration | Cooldown and failure behavior are correct |
| Account | Test authenticated favorites/vehicle/history endpoints | User scoping is enforced |
| Dashboard | Open the MechanicYab dashboard in Admin | Only owned mechanics are shown |
| Moderation | Open the Review moderation page with the correct capability | Status transitions require the moderation capability and nonce |
| Gallery | Upload/select a staging attachment and add it to a mechanic gallery | Attachment boundary and ownership checks pass |
| Review | Submit, moderate, report, and reply to a review | State and ownership rules are enforced |
| Migration | Run `wp mechanicyab migrate` a second time | Idempotent; no destructive change |
| Logs | Review PHP, web-server, and WordPress logs | No new warnings or fatal errors attributable to the plugin |

## 7. Provider Verification Checklists

### SMS

Use a provider sandbox or a controlled staging number. Verify successful delivery, invalid-number rejection, cooldown enforcement, attempt limits, single-use OTP behavior, provider timeout behavior, and that OTP values never appear in logs. Keep the provider disabled until these checks pass.

### Payment

Use a provider sandbox merchant and a non-production callback URL. Verify payment start, callback signature/reference handling, stored-amount comparison, duplicate callback behavior, failed verification, timeout behavior, and ledger consistency. Never use production money or production credentials in staging.

### AI

Use a provider sandbox or a controlled test adapter. Verify prompt-injection rejection, context minimization, credit reservation/consumption behavior, provider failure without unintended charging, timeout handling, and absence of sensitive data in logs. Do not add a provider secret to the repository.

### Backup and Restore

Take a database dump and an uploads backup before migration. Restore both into a separate disposable WordPress instance. Confirm schema health, public resource rendering, user identity references, and media references after restore. Record the restore duration and any manual steps.

### Load and Performance

Run the repository preflight scripts first:

```bash
./scripts/l2-runtime-preflight.sh
./scripts/performance-preflight.sh
```

Then execute authenticated and anonymous load tests against staging. Include search, public mechanic pages, REST health, analytics ingestion, and account endpoints. Capture latency percentiles, error rate, database CPU, slow queries, PHP workers, memory, and cache behavior. No production readiness claim may be based only on local tests.

## 8. Migration and Rollback Procedure

### Forward migration

1. Put staging in a maintenance or restricted-access state.
2. Capture a database dump and uploads backup.
3. Verify the exact release commit and dependency lockfile.
4. Deploy plugin and theme files.
5. Run `composer install` in the plugin directory.
6. Activate or update the plugin.
7. Run `wp mechanicyab migrate`.
8. Run `wp mechanicyab health` and inspect the schema version.
9. Flush rewrites.
10. Execute the smoke checklist.
11. Remove maintenance mode only after health and smoke checks pass.

### Rollback

The plugin's rollback plan is non-destructive. It restores the previous code release and does not drop or reset domain tables.

1. Restrict staging traffic.
2. Preserve current logs and database state for diagnosis.
3. Restore the previous plugin/theme code and its lockfile.
4. Run the previous release's dependency installation.
5. Do not reduce the schema version or drop v11 tables.
6. Restore the database and uploads only when the incident requires data rollback and the restore point is verified.
7. Flush rewrites and run health checks.
8. Record whether the rollback was code-only or code-plus-data.

A destructive database rollback is an incident action and requires an explicit database backup, restoration test, and approval under the deployment policy. The repository does not contain an automatic destructive rollback command.

## 9. Completion Gate for Staging

Staging is ready for product testing when the exact commit is installed, migration is idempotent, health is healthy, smoke checks pass, logs are clean, permissions are verified, and provider tests are either passed or explicitly marked disabled with no accidental production calls.

Staging is not production-ready until security review, backup/restore evidence, provider verification, performance/load evidence, monitoring, deployment rollback rehearsal, and operational ownership are complete.

## 10. Current Runtime Gaps

The repository sandbox has not supplied evidence for WordPress activation, MySQL `dbDelta`, browser/Admin E2E, real media storage, SMS delivery, payment sandbox callbacks, AI provider calls, backup restoration, load testing, or production deployment. These are the exact next verification activities after staging is available.

## References

[1]: https://developer.wordpress.org/cli/commands/ "WP-CLI Command Reference"

[2]: https://developer.wordpress.org/plugins/creating-tables-with-plugins/ "WordPress Plugin Database Tables"

[3]: https://developer.wordpress.org/apis/cron/ "WordPress Cron API"
