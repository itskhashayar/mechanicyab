# Stage 6 — Public UX & SEO-ready Shell

## Architecture self-review

The Theme remains presentation-only. It owns design tokens, RTL/mobile-first CSS, accessible templates and semantic shell markup. Core Plugin owns public route resolution, REST contracts and ViewModel boundaries. No business query, persistence, permission check or domain decision was moved into the Theme.

## Implemented scope

The Theme now contains `theme.json` design tokens, RTL/mobile-first styling, accessible header/footer, front page, search, map and mechanic presentation shells. Core registers route resolution for `/search`, `/map` and `/mechanic/{slug}` through Rewrite API and resolves templates through the active Theme. Search and Map data remain supplied by Core-owned REST/Service contracts.

The shell supports crawlable semantic HTML, skip links, keyboard focus states, responsive layouts and explicit empty/loading-oriented presentation copy. It does not create independent SEO records or duplicate Domain data.

## Verification

Local Composer validation, PHP syntax checks, PHPUnit (27 tests, 52 assertions), Theme boundary scan and Stage scope scan pass. L2 WordPress route/template runtime, browser E2E, accessibility automation and SEO crawler verification remain `Not Verified` without a real staging runtime.

## Recovery / disable path

Theme templates are presentation-only and can fall back to `index.php`. Core route registration is isolated in `PublicRouteResolver`; disabling that registration leaves WordPress default template resolution and does not delete domain data.
