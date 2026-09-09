#!/usr/bin/env bash
set -euo pipefail
printf 'Performance preflight\n'
printf 'PHP: '; php -v | head -1
printf 'Composer: '; composer --version | head -1
printf 'Repository: '; git rev-parse --short HEAD
printf 'Runtime benchmark: Not Runtime Verified (WordPress/MySQL unavailable)\n'
printf 'Required future evidence: EXPLAIN plans, cache hit ratio, queue latency, p95/p99 request latency, load profile.\n'
