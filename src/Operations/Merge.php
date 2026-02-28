<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Utils\Arr;

class Merge extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $result = [];
        foreach (Arr::wrapNull($rules) as $rule) {
            $evaluated = static::evaluate($rule, $scopeStack);
            foreach (Arr::wrapNull($evaluated) as $v) {
                $result[] = $v;
            }
        }
        return $result;
    }
}
