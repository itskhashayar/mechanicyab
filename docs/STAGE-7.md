# Stage 7 — Trust & Moderation

## Architecture self-review

Reviews are the Source of Truth for individual user experiences. Mechanics `average_rating` and `review_count` remain derived summaries rebuilt from approved active reviews; they are not independently writable trust data and are not used here to invent ranking or eligibility rules. Review moderation is separate from mechanic verification.

## Implemented scope

Schema version `5` adds `reviews`, `review_replies`, `review_reports` and `moderation_cases`. The Core Plugin includes a Review repository contract, prepared WordPress repository, submission validation, moderation state transitions, reporting, approved-only public resource reads and summary rebuild orchestration. Public REST contracts include authenticated submission/reporting and public approved Review reads.

Review authors are represented with `wp_user_id`, preserving WordPress as the canonical identity source. Public resources never expose author identity. REST errors do not expose internal exceptions.

## Explicit exclusions

Full Admin moderation UI, abuse scoring, verified-visit proof, review replies UI, fraud detection, ranking policy changes and auction/eligibility rules are not implemented here. They remain governed by their later Operations or Monetization stages.

## Verification

Local Composer validation, PHP syntax checks and PHPUnit regression pass. L2 WordPress REST/runtime, L3 MySQL migration and browser abuse-flow verification remain `Not Verified` without staging evidence.

## Recovery / disable path

Review submission and public routes can be disabled independently at the REST registration boundary. Existing Review data is not deleted. Summary rebuild is explicit and repeatable; failed moderation or rebuild does not alter the Review Source of Truth automatically.
