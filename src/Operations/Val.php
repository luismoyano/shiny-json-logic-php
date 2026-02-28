<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Utils\Arr;
use ShinyJsonLogic\Utils\DataArray;

class Val extends AbstractOperation
{
    public static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $rawKeys = Arr::wrapNull($rules);

        if (empty($rawKeys) || $rawKeys === [null]) {
            return DataArray::wrap($scopeStack->current());
        }

        $firstKey = $rawKeys[0];

        // Scope navigation: first element is an array e.g. [[1], "key"]
        if (is_array($firstKey) && !Arr::isAssoc($firstKey)) {
            $levelIndicator = (int)($firstKey[0] ?? 0);
            $remainingKeys = array_slice($rawKeys, 1);
            $evaluatedKeys = array_map(fn($rule) => static::evaluate($rule, $scopeStack), $remainingKeys);
            $levels = abs($levelIndicator);
            return DataArray::wrap($scopeStack->resolve($levels, ...$evaluatedKeys));
        }

        // Normal case
        $keys = array_map(fn($rule) => static::evaluate($rule, $scopeStack), $rawKeys);
        $currentData = $scopeStack->current();
        return DataArray::wrap(self::digValue($currentData, $keys));
    }

    private static function digValue(mixed $data, array $keys): mixed
    {
        if ($data === null) {
            return null;
        }
        if (empty($keys)) {
            return $data;
        }
        foreach ($keys as $key) {
            if ($data === null) {
                return null;
            }
            if (is_array($data) && Arr::isAssoc($data)) {
                $data = $data[(string)$key] ?? null;
            } elseif (is_array($data)) {
                $data = $data[(int)$key] ?? null;
            } else {
                return null;
            }
        }
        return $data;
    }
}
