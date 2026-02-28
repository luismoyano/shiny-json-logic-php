<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;

class Coalesce extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        if (!is_array($rules)) {
            return null;
        }

        foreach ($rules as $rule) {
            $result = static::evaluate($rule, $scopeStack);
            if ($result !== null) {
                return $result;
            }
        }
        return null;
    }
}
