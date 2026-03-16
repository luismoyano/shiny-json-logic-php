<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Numericals\Numerify;
use ShinyJsonLogic\Utils\Arr;

class Subtraction extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $operands = Arr::wrapNull($rules);
        if (empty($operands)) {
            static::handleInvalidArgs();
        }

        return static::safeArithmetic(function () use ($operands, $scopeStack) {
            $result = null;
            $count = 0;

            foreach ($operands as $rule) {
                $evaluated = static::evaluate($rule, $scopeStack);
                $num = Numerify::numerify($evaluated);
                if ($num === null) {
                    $num = 0;
                }
                $count++;
                $result = ($result === null) ? $num : $result - $num;
            }

            if ($count === 0) {
                static::handleInvalidArgs();
            }
            if ($count === 1) {
                $negated = $result * -1;
                // Avoid -0.0: PHP's float negation of 0.0 produces -0.0 which
                // serialises to "-0" in JSON, but the spec expects 0.
                return $negated === 0.0 ? 0 : $negated;
            }

            return $result;
        });
    }
}
