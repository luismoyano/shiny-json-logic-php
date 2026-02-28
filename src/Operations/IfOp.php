<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\Engine;
use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Truthy;

class IfOp extends AbstractOperation
{
    public static function call(mixed $rules, ScopeStack $scopeStack): mixed
    {
        if (!is_array($rules)) {
            static::handleInvalidArgs();
        }

        $chunks = array_chunk($rules, 2);
        foreach ($chunks as $pair) {
            $conditionRule = $pair[0];
            $valueRule = $pair[1] ?? null;

            $conditionResult = Engine::evaluate($conditionRule, $scopeStack);

            if ($valueRule === null) {
                return $conditionResult;
            }

            if (Truthy::isTruthy($conditionResult)) {
                return Engine::evaluate($valueRule, $scopeStack);
            }
        }

        return null;
    }
}
