# shiny/json-logic-php ✨

[![Packagist](https://img.shields.io/packagist/v/shiny/json-logic-php)](https://packagist.org/packages/shiny/json-logic-php)
[![PHP](https://img.shields.io/badge/php-%3E%3D8.1-blue)](https://www.php.net)
[![License: MIT](https://img.shields.io/badge/license-MIT-green)](LICENSE)

> **The most compliant PHP implementation of JSON Logic. ✨**

**shiny/json-logic-php** is a **pure PHP**, **zero-dependency** JSON Logic implementation — the only PHP library that passes 100% of the official JSON Logic tests (in stdclass mode), with no external dependencies and PHP 8.1+ compatibility.

This is the PHP port of [shiny_json_logic](https://rubygems.org/gems/shiny_json_logic), the most compliant Ruby implementation of JSON Logic.

---

## Why shiny/json-logic-php?

The existing PHP JSON Logic library ([jwadhams/json-logic-php](https://github.com/jwadhams/json-logic-php)) covers ~69% of the official test suite and hasn't seen active maintenance. If you've hit edge cases with missing operators, incorrect truthiness, or subtle bugs — this is the fix.

- ✅ **100% spec-compliant** — the only PHP library that passes all 601 official JSON Logic tests (stdclass mode).
- 🧩 **Zero runtime dependencies** — stdlib only. Just plug & play.
- 🕰️ **PHP 8.1+** compatible.
- 🔧 **Actively maintained** and aligned with the evolving JSON Logic specification.
- 🔁 **Drop-in aliases**: `JsonLogic` and `JSONLogic` available out of the box.

---

## Installation

```bash
composer require shiny/json-logic-php
```

---

## Usage

```php
use ShinyJsonLogic\ShinyJsonLogic;

// Basic evaluation
ShinyJsonLogic::apply(['==' => [1, 1]]);
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
```

### Nested logic

Rules can be nested arbitrarily:

```php
$rule = [
    'if' => [
        ['var' => 'financing'],
        ['missing' => ['apr']],
        []
    ]
];

ShinyJsonLogic::apply($rule, ['financing' => true]);
// → ["apr"]
```

### Drop-in aliases

`JsonLogic` and `JSONLogic` are available as aliases:

```php
JsonLogic::apply(['>' => [['var' => 'score'], 90]], ['score' => 95]);
// → true
```

### Working with `json_decode`

In PHP, `json_decode` parses a JSON string into either stdclass objects or associative arrays:

```php
json_decode('{"var": "name"}');        // → stdClass { $var: "name" }
json_decode('{"var": "name"}', true);  // → ["var" => "name"]
```

Both modes work with this library:

```php
// stdclass mode (recommended — full 601/601 compliance)
$rule = json_decode('{"var": "name"}');
$data = json_decode('{"name": "Luis"}');
ShinyJsonLogic::apply($rule, $data);
// → "Luis"

// arrays mode also works (600/601 — see Compatibility)
$rule = json_decode('{"var": "name"}', true);
$data = json_decode('{"name": "Luis"}', true);
ShinyJsonLogic::apply($rule, $data);
// → "Luis"
```

---

## Supported operators

| Category | Operators |
|----------|-----------|
| Data access | `var`, `missing`, `missing_some`, `exists`, `val` ✨ |
| Logic | `if`, `?:`, `and`, `or`, `!`, `!!` |
| Comparison | `==`, `===`, `!=`, `!==`, `>`, `>=`, `<`, `<=` |
| Arithmetic | `+`, `-`, `*`, `/`, `%`, `max`, `min` |
| String | `cat`, `substr` |
| Array | `map`, `filter`, `reduce`, `all`, `none`, `some`, `merge`, `in` |
| Coalesce | `??` ✨ |
| Error handling | `throw`, `try` ✨ |
| Utility | `coalesce`, `preserve` ✨, `log` |

✨ = community-extended operators beyond the core spec.

---

## Error handling

shiny/json-logic-php uses native PHP exceptions:

```php
// Unknown operators throw an exception
ShinyJsonLogic::apply(['unknown_op' => [1, 2]], []);
// → throws ShinyJsonLogic\Errors\UnknownOperator

// You can use try/throw for controlled error handling within rules
$rule = [
    'try' => [
        ['throw' => 'Something went wrong'],
        ['cat' => ['Error: ', ['var' => 'type']]]
    ]
];
ShinyJsonLogic::apply($rule, []);
// → "Error: Something went wrong"
```

Exception classes:
- `ShinyJsonLogic\Errors\UnknownOperator` — unknown operator in rule
- `ShinyJsonLogic\Errors\InvalidArguments` — invalid arguments to operator
- `ShinyJsonLogic\Errors\NotANumber` — NaN result in numeric operation

Or catch `ShinyJsonLogic\Errors\Base` to handle all library errors in one sweep.

---

## Compatibility

Tested against the [official JSON Logic test suite](https://github.com/json-logic/.github/tree/main/tests) (601 tests).

| Mode | Passed | Notes |
|------|--------|-------|
| **stdclass** (`json_decode` without `true`) | **601 / 601** | Full compliance |
| **arrays** (`json_decode` with `true`) | **600 / 601** | One PHP-language limitation (see below) |

### The arrays mode edge case

In PHP, `json_decode('{}', true)` returns `[]` — an empty array, indistinguishable from `json_decode('[]', true)`. This means that in arrays mode, the engine cannot tell an empty object from an empty array.

The one failing case is the `+` operator with an empty object passed directly as the operand list:

```php
// stdclass mode — throws NotANumber ✅
ShinyJsonLogic::apply(json_decode('{"+" : {}}'));

// arrays mode — returns 0 instead of NaN ❌
ShinyJsonLogic::apply(json_decode('{"+" : {}}', true));
```

In arrays mode, `{"+" : {}}` is decoded as `["+" => []]` — zero operands — so `+` returns `0` instead of NaN. In stdclass mode, `{}` is preserved as an `EmptyObject` sentinel, and the operator correctly produces NaN.

This is a PHP language limitation, not a bug in this library. The issue has been [reported to the json-logic org](https://github.com/orgs/json-logic/discussions/48).

For this reason, if you need full spec compliance or interoperability with other JSON Logic implementations, we strongly recommend using stdclass mode (`json_decode` without `true`).

All other 600 tests pass in both modes.

---

## Development

```bash
composer install
./vendor/bin/phpunit
```

To run the official test suite against live test data:

```bash
./run-official-tests.sh
```

Requires Docker and curl. Fetches the official tests at runtime from `github.com/json-logic/.github`.

---

## Contributing

Contributions are welcome — especially:

- spec alignment improvements
- missing operators
- edge-case tests

Please include tests with any change.

Repository: https://github.com/luismoyano/shiny-json-logic-php

---

## Related projects

- [shiny_json_logic (Ruby)](https://rubygems.org/gems/shiny_json_logic) — 601/601 official tests, the most compliant Ruby implementation
- [jsonlogicruby.com](https://jsonlogicruby.com) — JSON Logic playground, docs, and specification reference

---

## License

MIT License.

Use it. Fork it. Ship it. (:

---

> Shine bright like a 🐘
