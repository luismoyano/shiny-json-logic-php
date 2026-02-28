<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Truthy;

class OrOp extends AbstractOperation
{
    protected static function throwOnDynamicArgs(): bool { return true; }

    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        if (!is_array($rules)) {
            static::handleInvalidArgs();
        }
        if (empty($rules)) {
            return false;
        }
        $result = null;
        foreach ($rules as $rule) {
            $result = static::evaluate($rule, $scopeStack);
            if (Truthy::isTruthy($result)) {
                return $result;
            }
        }
        return $result;
    }
}
