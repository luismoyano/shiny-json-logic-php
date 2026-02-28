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
#   ./run-official-tests.sh [--verbose|-v]
#
# Requirements: curl, docker

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
VERBOSE=0

for arg in "$@"; do
  case "$arg" in
    --verbose|-v) VERBOSE=1 ;;
  esac
done

BASE_URL="https://raw.githubusercontent.com/json-logic/.github/main/tests"

TEST_FILES=(
  "arithmetic/divide.json"
  "arithmetic/minus.json"
  "arithmetic/modulo.json"
  "arithmetic/multiply.json"
  "arithmetic/plus.json"
  "coalesce.json"
  "empty-objects.json"
  "exists.json"
  "legacy.json"
  "max.json"
  "min.json"
  "throw.json"
  "truthiness.json"
  "try.json"
  "unknown-operators.json"
  "val.json"
)

# Build the Docker image if needed (uses cache when nothing changed)
docker build -q -t shiny-json-logic-php "$SCRIPT_DIR" >/dev/null

# Fetch all test files once into a temp directory
CACHE_DIR=$(mktemp -d)
trap 'rm -rf "$CACHE_DIR"' EXIT

for file in "${TEST_FILES[@]}"; do
  dir=$(dirname "$CACHE_DIR/$file")
  mkdir -p "$dir"
  curl -sSf "$BASE_URL/$file" > "$CACHE_DIR/$file"
done

# run_suite MODE
# Prints FAIL lines directly, echoes "pass=N fail=N time=Xms" as last line
run_suite() {
  local mode="$1"
  local total_pass=0
  local total_fail=0
  local total_time_ms="0"

  for file in "${TEST_FILES[@]}"; do
    output=$(cat "$CACHE_DIR/$file" \
      | docker run --rm -i shiny-json-logic-php \
          php -d "intl.default_locale=en_US" bin/run-official-tests-runner.php /app "$file" "$VERBOSE" "$mode" \
          2>&1) || true

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
  done

  echo "pass=$total_pass fail=$total_fail time=${total_time_ms}ms"
}

echo ""
echo "=== Mode: arrays (json_decode with true) ==="
ARRAYS_SUMMARY=$(run_suite "arrays")
echo ""
echo "=== Mode: stdclass (json_decode without true) ==="
STDCLS_SUMMARY=$(run_suite "stdclass")

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
