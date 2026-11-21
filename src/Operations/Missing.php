<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Utils\Arr;

class Missing extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $items = Arr::wrapNull($rules);
        $keys = [];
        foreach ($items as $rule) {
            $evaluated = static::evaluate($rule, $scopeStack);
            foreach (Arr::wrapNull($evaluated) as $v) {
                $keys[] = (string)$v;
            }
        }

        $currentData = $scopeStack->current();
        if (!is_array($currentData)) {
            return $keys;
        }

        $missing = [];
        foreach ($keys as $key) {
            if (!self::isPresent($currentData, $key)) {
                $missing[] = $key;
            }
        }
        return $missing;
    }

    /**
     * Walks a dotted path ("user.address.city", "items.0") through the data.
     * A key counts as present when it exists, whatever its value.
     */
    protected static function isPresent(array $data, string $key): bool
    {
        $current = $data;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }
            $current = $current[$segment];
        }
        return true;
    }
}
