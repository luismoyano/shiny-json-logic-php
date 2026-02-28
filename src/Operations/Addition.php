<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Numericals\Numerify;
use ShinyJsonLogic\Utils\Arr;

class Addition extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        return static::safeArithmetic(function () use ($rules, $scopeStack) {
            $result = 0.0;
            foreach (Arr::wrapNull($rules) as $rule) {
                $val = Numerify::numerify(static::evaluate($rule, $scopeStack));
                $result += ($val === null ? 0 : $val);
            }
            return $result;
        });
    }
}
