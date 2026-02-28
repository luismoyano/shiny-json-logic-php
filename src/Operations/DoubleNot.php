<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Truthy;
use ShinyJsonLogic\Utils\Arr;

class DoubleNot extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $value = Arr::wrapNull($rules)[0] ?? null;
        return (bool)Truthy::isTruthy(static::evaluate($value, $scopeStack));
    }
}
