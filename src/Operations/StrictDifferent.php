<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Comparisons\Comparable;
use ShinyJsonLogic\Utils\Arr;

class StrictDifferent extends AbstractOperation
{
    protected static function throwOnDynamicArgs(): bool { return true; }

    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $operands = Arr::wrapNull($rules);
        if (count($operands) < 2) {
            static::handleInvalidArgs();
        }

        $prev = Comparable::cast(static::evaluate($operands[0], $scopeStack));
        $rest = array_slice($operands, 1);
        foreach ($rest as $rule) {
            $curr = Comparable::cast(static::evaluate($rule, $scopeStack));
            if ($curr === $prev) {
                return false;
            }
            $prev = $curr;
        }
        return true;
    }
}
