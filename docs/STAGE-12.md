# Stage 12 — SEO, Content & Local Growth

The Core Plugin owns canonical URL normalization, structured-data view models and bounded sitemap chunks. The Theme only presents these outputs. Product routes remain controlled by the existing route resolver; no uncontrolled programmatic page generator is introduced.

The sitemap service caps a chunk at 50,000 URLs, normalizes canonical trailing slashes and sanitizes schema fields. Thin-page prevention, indexation strategy, redirect inventory and real search-engine validation require staging and are `Not Runtime Verified`.

Local verification includes Composer, syntax and SEO contract tests. Production SEO release requires canonical, robots, sitemap, structured data, duplicate-content and browser checks.
