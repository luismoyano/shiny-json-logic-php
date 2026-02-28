<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Truthy;
use ShinyJsonLogic\Utils\Arr;

class NotOp extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        // If rules is an assoc array, it's already-resolved data (resolveRules evaluated any op).
        // Don't re-evaluate it — just use it directly.
        if (is_array($rules) && Arr::isAssoc($rules)) {
            return !Truthy::isTruthy($rules);
        }
        $value = is_array($rules) ? ($rules[0] ?? null) : $rules;
        return !Truthy::isTruthy(static::evaluate($value, $scopeStack));
    }
}
