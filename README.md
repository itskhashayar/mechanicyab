# MechanicYab

بهترین مکانیک محله‌ات کجاست؟

این repository، Workspace رسمی Greenfield پروژه مکانیک‌یاب است.

## Architecture

- `plugins/mechanicyab-core/` — MechanicYab Core Plugin؛ مالک Foundation، Moduleها، Contracts، API، Health، Settings و Permissions.
- `theme/mechanicyab-theme/` — Presentation-only Theme؛ بدون Business Logic و Domain Query.
- `docs/` — مستندات اجرایی و Decision/Verification notes.

File 12، یعنی **WordPress Master Architecture & Implementation Specification**، مرجع Implementation است.

## Stage 1

Stage 1 فقط Foundation است. Domain Feature، Domain Custom Table، CPT/Post Meta workaround، Provider واقعی، Secret، Payment، AI، Search، Analytics و Production Deployment در این Stage ساخته نمی‌شوند.

## Local checks

```bash
composer validate --strict --no-check-publish plugins/mechanicyab-core/composer.json
composer install --working-dir=plugins/mechanicyab-core
find plugins theme -type f -name '*.php' -print0 | xargs -0 -n1 php -l
```

برای تست Foundation، PHPUnit در CI نصب و اجرا می‌شود.

## Git workflow

تغییرات Stage 1 در branch `feature/stage-1-foundation` توسعه داده می‌شوند و پس از تست و بررسی به‌صورت Pull Request ارائه خواهند شد.

## Security

Secret، API key، password، token و credential نباید داخل repository قرار بگیرند.

## Runtime preflight

L2 requires a real non-production WordPress installation and does not use a mock. Set `WP_PATH` and run:

```bash
WP_PATH=/path/to/wordpress bash scripts/l2-runtime-preflight.sh
```

If PHP, WP-CLI, or a real WordPress path is unavailable, the script exits with `BLOCKED` and no Runtime Verified claim is made.
