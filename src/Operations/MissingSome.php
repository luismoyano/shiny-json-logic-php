<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Utils\Arr;

class MissingSome extends Missing
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        if (!is_array($rules)) {
            return [];
        }

        $minRequired = static::evaluate($rules[0], $scopeStack);
        $keysRaw = Arr::wrapNull(static::evaluate($rules[1] ?? null, $scopeStack));
        $keys = array_map('strval', $keysRaw);

        $currentData = $scopeStack->current();
        if (!is_array($currentData)) {
            return $keys;
        }

        $dataKeys = array_map('strval', array_keys($currentData));
        $present = array_values(array_intersect($keys, $dataKeys));

        if (count($present) >= $minRequired) {
            return [];
        }

        return parent::execute($keys, $scopeStack);
    }
}
