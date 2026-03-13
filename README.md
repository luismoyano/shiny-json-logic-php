# shiny/json-logic-php

A modern, complete PHP implementation of the [JSON Logic](https://jsonlogic.com) specification.

[![Packagist](https://img.shields.io/packagist/v/shiny/json-logic-php)](https://packagist.org/packages/shiny/json-logic-php)
[![PHP](https://img.shields.io/badge/php-%3E%3D7.4-blue)](https://www.php.net)
[![License: MIT](https://img.shields.io/badge/license-MIT-green)](LICENSE)

## Features

- **601/601 official tests passing** (stdclass mode — see [Compatibility](#compatibility))
- **Zero runtime dependencies** — stdlib only
- **PHP 7.4+** compatible
- Drop-in aliases: `JsonLogic` and `JSONLogic` available out of the box
- Supports all core and community-extended operators: `var`, `missing`, `missing_some`, `if`, `==`, `===`, `!=`, `!==`, `!`, `!!`, `and`, `or`, `>`, `>=`, `<`, `<=`, `max`, `min`, `+`, `-`, `*`, `/`, `%`, `map`, `filter`, `reduce`, `all`, `none`, `some`, `merge`, `in`, `cat`, `substr`, `log`, `exists`, `val`, `coalesce`, `preserve`, `throw`, `try`

## Installation

```bash
composer require shiny/json-logic-php
```

## Usage

```php
use ShinyJsonLogic\ShinyJsonLogic;

// Basic evaluation
ShinyJsonLogic::apply(['==', [1, 1]]);
// → true

// With data
ShinyJsonLogic::apply(['var' => 'name'], ['name' => 'Luis']);
// → "Luis"

// Feature flag example
$rule = ['==' => [['var' => 'plan'], 'premium']];
$data = ['plan' => 'premium'];
ShinyJsonLogic::apply($rule, $data);
// → true

// Nested access
ShinyJsonLogic::apply(['var' => 'user.age'], ['user' => ['age' => 30]]);
// → 30

// Drop-in aliases also work
JsonLogic::apply(['>' => [['var' => 'score'], 90]], ['score' => 95]);
// → true
```

### Working with `json_decode`

If you decode JSON without the `true` flag (stdClass mode), it works directly:

```php
$rule = json_decode('{"var": "name"}');
$data = json_decode('{"name": "Luis"}');
ShinyJsonLogic::apply($rule, $data);
// → "Luis"
```

If you use `json_decode($json, true)` (arrays mode), it also works — see [Compatibility](#compatibility) for the one known edge case.

## Compatibility

shiny/json-logic-php is tested against the [official JSON Logic test suite](https://github.com/json-logic/.github/tree/main/tests) (601 tests).

| Mode | Passed | Notes |
|------|--------|-------|
| **stdclass** (`json_decode` without `true`) | **601 / 601** | Full compliance |
| **arrays** (`json_decode` with `true`) | **600 / 601** | One PHP-language limitation (see below) |

### The arrays mode edge case

In PHP, `json_decode('{}', true)` returns `[]` — an empty array, indistinguishable from `json_decode('[]', true)`. This means that in arrays mode, the engine cannot tell an empty object from an empty list, which causes one test case (empty object as `+` operand producing NaN) to behave differently from the spec.

This is a PHP language limitation, not a bug in this library. The [json-logic org is aware](https://github.com/json-logic/.github/blob/main/ACCEPTED_PROPOSALS.md) of per-language constraints. If your use case involves distinguishing empty objects from empty arrays at the top level, use stdclass mode.

All other 600 tests pass in both modes.

## Operators

All standard JSON Logic operators are supported:

| Category | Operators |
|----------|-----------|
| Data access | `var`, `missing`, `missing_some`, `exists`, `val` |
| Logic | `if`, `?:`, `and`, `or`, `!`, `!!` |
| Comparison | `==`, `===`, `!=`, `!==`, `>`, `>=`, `<`, `<=` |
| Arithmetic | `+`, `-`, `*`, `/`, `%`, `max`, `min` |
| String | `cat`, `substr` |
| Array | `map`, `filter`, `reduce`, `all`, `none`, `some`, `merge`, `in` |
| Error handling | `throw`, `try` |
| Utility | `coalesce`, `preserve`, `log` |

## Running the official test suite

```bash
./run-official-tests.sh
```

Requires Docker and curl. Fetches the official tests at runtime from `github.com/json-logic/.github`.

## Related projects

- [shiny_json_logic (Ruby)](https://rubygems.org/gems/shiny_json_logic) — 601/601 official tests, the most compliant Ruby implementation
- [jsonlogicruby.com](https://jsonlogicruby.com) — JSON Logic playground, docs, and specification reference

## License

MIT
