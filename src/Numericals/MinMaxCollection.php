<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Numericals;

use ShinyJsonLogic\Engine;
use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Utils\Arr;
use ShinyJsonLogic\Errors\InvalidArguments;

class MinMaxCollection
{
    public static function collectNumericValues(mixed $rules, ScopeStack $scopeStack): array
    {
        $values = self::collectValues($rules, $scopeStack);
        if (empty($values)) {
            throw new InvalidArguments();
        }
        foreach ($values as $v) {
            if (!is_int($v) && !is_float($v)) {
                throw new InvalidArguments();
            }
        }
        return $values;
    }

    public static function collectValues(mixed $rules, ScopeStack $scopeStack): array
    {
        // If rules is an operation (assoc array with a known operator key), evaluate it
        if (is_array($rules) && !empty($rules) && Arr::isAssoc($rules)) {
            $evaluated = Engine::evaluate($rules, $scopeStack);
            return Arr::wrapNull($evaluated);
        }

        $result = [];
        foreach (Arr::wrapNull($rules) as $rule) {
            $result[] = Engine::evaluate($rule, $scopeStack);
        }
        return $result;
    }
}
