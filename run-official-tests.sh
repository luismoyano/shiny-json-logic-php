#!/usr/bin/env bash
#
# Fetches the official json-logic test suite from GitHub at runtime and runs
# every test case against this PHP implementation.
#
# Usage:
#   ./run-official-tests.sh [--verbose|-v]
#
# Requirements: curl, docker
#
# Tests are never stored on disk — they are fetched fresh from upstream on
# every run so the suite always reflects the current state of the spec.

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

total_pass=0
total_fail=0

for file in "${TEST_FILES[@]}"; do
  json=$(curl -sSf "$BASE_URL/$file")

  output=$(printf '%s' "$json" \
    | docker run --rm -i shiny-json-logic-php \
        php bin/run-official-tests-runner.php /app "$file" "$VERBOSE" \
        2>&1) || true

  summary=$(printf '%s\n' "$output" | tail -n1)
  body=$(printf '%s\n' "$output" | sed '$d')

  # Parse "SUMMARY pass=N fail=N"
  pass=$(echo "$summary" | sed 's/.*pass=\([0-9]*\).*/\1/')
  fail=$(echo "$summary" | sed 's/.*fail=\([0-9]*\)/\1/')

  total_pass=$((total_pass + pass))
  total_fail=$((total_fail + fail))

  if [ "$fail" -gt 0 ] || [ "$VERBOSE" -eq 1 ]; then
    [ -n "$body" ] && printf '%s\n' "$body"
  fi
done

echo ""
echo "Results: $total_pass passed, $total_fail failed"

[ "$total_fail" -eq 0 ]
