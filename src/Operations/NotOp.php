<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Truthy;

class NotOp extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $value = is_array($rules) ? ($rules[0] ?? null) : $rules;
        return !Truthy::isTruthy(static::evaluate($value, $scopeStack));
    }
}
