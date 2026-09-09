#!/usr/bin/env bash
set -euo pipefail

WP_PATH="${WP_PATH:-}"

if ! command -v php >/dev/null 2>&1; then
  echo "BLOCKED: PHP CLI is unavailable."
  exit 2
fi
if ! command -v wp >/dev/null 2>&1; then
  echo "BLOCKED: WP-CLI is unavailable."
  exit 2
fi
if [[ -z "$WP_PATH" || ! -f "$WP_PATH/wp-load.php" ]]; then
  echo "BLOCKED: Set WP_PATH to a real WordPress installation."
  exit 2
fi

wp --path="$WP_PATH" core version
wp --path="$WP_PATH" plugin verify-checksums --all || true
printf '%s\n' "L2 preflight prerequisites detected. Run the WordPress activation matrix against this non-production install."
