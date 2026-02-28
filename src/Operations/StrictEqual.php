<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Comparisons\Comparable;
use ShinyJsonLogic\Utils\Arr;

class StrictEqual extends AbstractOperation
{
    protected static function throwOnDynamicArgs(): bool { return true; }

    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $operands = Arr::wrapNull($rules);
        if (count($operands) < 2) {
            static::handleInvalidArgs();
        }

        $first = Comparable::cast(static::evaluate($operands[0], $scopeStack));
        $rest = array_slice($operands, 1);
        foreach ($rest as $rule) {
            if (Comparable::cast(static::evaluate($rule, $scopeStack)) !== $first) {
                return false;
            }
        }
        return true;
    }
}
