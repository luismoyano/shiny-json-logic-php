<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Numericals\Numerify;
use ShinyJsonLogic\Utils\Arr;

class Division extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $operands = Arr::wrapNull($rules);
        if (empty($operands)) {
            static::handleInvalidArgs();
        }

        $result = null;
        $count = 0;

        try {
            foreach ($operands as $rule) {
                $evaluated = static::evaluate($rule, $scopeStack);
                $num = Numerify::numerify($evaluated);
                if ($num === null) {
                    static::handleNan();
                }
                $count++;
                $result = ($result === null) ? $num : $result / $num;
            }
        } catch (\TypeError $e) {
            static::handleNan();
        }

        if ($count === 0) {
            static::handleInvalidArgs();
        }

        $finalResult = ($count === 1) ? 1.0 / $result : $result;

        return static::safeArithmetic(fn() => $finalResult);
    }
}
