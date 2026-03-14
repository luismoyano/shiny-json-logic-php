#!/usr/bin/env bash
#
# Fetches the official json-logic test suite from GitHub at runtime and runs
# every test case against this PHP implementation.
#
# Runs the suite twice:
#   - arrays   mode: json_decode with true  (PHP arrays, standard usage)
#   - stdclass mode: json_decode without true (stdClass objects, preserves {} vs [])
#
# Usage:
#   ./run-official-tests.sh [--verbose|-v] [--no-docker] [--test-dir DIR]
#
# Options:
#   --verbose, -v      Print PASS lines in addition to FAIL lines
#   --no-docker        Run with the local php binary instead of Docker.
#                      Requires: php, composer dependencies installed (vendor/).
#                      Used by CI. Discovers test files dynamically via GitHub API.
#   --test-dir DIR     Use an existing directory of test files instead of downloading.
#                      Skips the download step entirely.
#
# Requirements (default): curl, docker
# Requirements (--no-docker): curl, php

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
VERBOSE=0
USE_DOCKER=1
TEST_DIR=""

for arg in "$@"; do
  case "$arg" in
    --verbose|-v) VERBOSE=1 ;;
    --no-docker)  USE_DOCKER=0 ;;
    --test-dir)   ;;  # handled below via shift-style parsing
  esac
done

# Parse --test-dir DIR (needs the next argument)
args=("$@")
for i in "${!args[@]}"; do
  if [ "${args[$i]}" = "--test-dir" ]; then
    TEST_DIR="${args[$i+1]}"
  fi
done

BASE_URL="https://raw.githubusercontent.com/json-logic/.github/main/tests"

# Tests that are impossible to pass due to PHP language limitations.
# In PHP, json_decode('{}', true) === json_decode('[]', true) === [],
# so empty objects are indistinguishable from empty arrays in arrays mode.
# Documented in README.md and reported to json-logic org (discussion #48).
ARRAYS_SKIP="Plus Operator with Single Operand, Direct Object Input Produces NaN"

# Fetch all test files once into a temp directory (or reuse --test-dir)
if [ -n "$TEST_DIR" ]; then
  CACHE_DIR="$(cd "$TEST_DIR" && pwd)"
else
  CACHE_DIR=$(mktemp -d)
  trap 'rm -rf "$CACHE_DIR"' EXIT

  # Download the entire tests/ directory dynamically via the git tree API.
  # Uses only curl + grep + sed — no gh CLI required.
  TREE_API="https://api.github.com/repos/json-logic/.github/git/trees/main?recursive=1"
  while read -r rel; do
    mkdir -p "$CACHE_DIR/$(dirname "$rel")"
    curl -sSf "$BASE_URL/$rel" > "$CACHE_DIR/$rel"
  done < <(curl -sSf "$TREE_API" \
    | grep '"path": "tests/.*\.json"' \
    | sed 's/.*"path": "tests\///;s/"[,]*//')
fi

# run_suite MODE
# Prints FAIL lines to stderr, echoes "pass=N fail=N time=Xms" as last line
run_suite() {
  local mode="$1"
  local total_pass=0
  local total_fail=0
  local total_time_ms="0"

  while IFS= read -r -d '' file; do
    local rel="${file#${CACHE_DIR}/}"

    if [ "$USE_DOCKER" -eq 1 ]; then
      output=$(docker run --rm -i shiny-json-logic-php \
        php -d "intl.default_locale=en_US" bin/run-official-tests-runner.php /app "$rel" "$VERBOSE" "$mode" "${SKIP:-}" \
        < "$file" 2>&1) || true
    else
      output=$(php -d "intl.default_locale=en_US" \
        "$SCRIPT_DIR/bin/run-official-tests-runner.php" "$SCRIPT_DIR" "$rel" "$VERBOSE" "$mode" "${SKIP:-}" \
        < "$file" 2>&1) || true
    fi

    summary=$(printf '%s\n' "$output" | tail -n1)
    body=$(printf '%s\n' "$output" | sed '$d')

    pass=$(echo "$summary" | sed 's/.*pass=\([0-9]*\).*/\1/')
    fail=$(echo "$summary" | sed 's/.*fail=\([0-9]*\).*/\1/')
    time_ms=$(echo "$summary" | sed 's/.*time=\([0-9.]*\)ms.*/\1/')

    total_pass=$((total_pass + pass))
    total_fail=$((total_fail + fail))
    total_time_ms=$(LC_ALL=C awk "BEGIN {printf \"%.2f\", $total_time_ms + $time_ms}")

    if [ "$fail" -gt 0 ] || [ "$VERBOSE" -eq 1 ]; then
      [ -n "$body" ] && printf '%s\n' "$body" >&2
    fi
  done < <(find "$CACHE_DIR" -name '*.json' -print0 | sort -z)

  echo "pass=$total_pass fail=$total_fail time=${total_time_ms}ms"
}

if [ "$USE_DOCKER" -eq 1 ]; then
  # Build the Docker image if needed (uses cache when nothing changed)
  docker build -q -t shiny-json-logic-php "$SCRIPT_DIR" >/dev/null
fi

echo ""
echo "=== Mode: arrays (json_decode with true) ==="
SKIP="$ARRAYS_SKIP" ARRAYS_SUMMARY=$(run_suite "arrays")
echo ""
echo "=== Mode: stdclass (json_decode without true) ==="
SKIP="" STDCLS_SUMMARY=$(run_suite "stdclass")

# Parse summaries
arrays_pass=$(echo "$ARRAYS_SUMMARY" | sed 's/pass=\([0-9]*\).*/\1/')
arrays_fail=$(echo "$ARRAYS_SUMMARY" | sed 's/.*fail=\([0-9]*\).*/\1/')
arrays_time=$(echo "$ARRAYS_SUMMARY" | sed 's/.*time=\([0-9.]*\)ms.*/\1/')
stdcls_pass=$(echo "$STDCLS_SUMMARY" | sed 's/pass=\([0-9]*\).*/\1/')
stdcls_fail=$(echo "$STDCLS_SUMMARY" | sed 's/.*fail=\([0-9]*\).*/\1/')
stdcls_time=$(echo "$STDCLS_SUMMARY" | sed 's/.*time=\([0-9.]*\)ms.*/\1/')

echo ""
echo "┌─────────────┬────────┬────────┬───────────┐"
echo "│ mode        │ passed │ failed │ time (ms) │"
echo "├─────────────┼────────┼────────┼───────────┤"
printf "│ %-11s │ %6s │ %6s │ %9s │\n" "arrays"   "$arrays_pass" "$arrays_fail" "${arrays_time}ms"
printf "│ %-11s │ %6s │ %6s │ %9s │\n" "stdclass" "$stdcls_pass" "$stdcls_fail" "${stdcls_time}ms"
echo "└─────────────┴────────┴────────┴───────────┘"

[ "$arrays_fail" -eq 0 ] && [ "$stdcls_fail" -eq 0 ]
