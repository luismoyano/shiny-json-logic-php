<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Numericals\Numerify;
use ShinyJsonLogic\Utils\Arr;

class Product extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $operands = Arr::wrapNull($rules);
        if (empty($operands)) {
            return 1;
        }

        return static::safeArithmetic(function () use ($operands, $scopeStack) {
            $result = null;
            $count = 0;

            foreach ($operands as $rule) {
                $evaluated = static::evaluate($rule, $scopeStack);
                $num = Numerify::numerify($evaluated);
                if ($num === null) {
                    static::handleNan();
                }
                $count++;
                $result = ($result === null) ? (float)$num : $result * (float)$num;
            }

            if ($count === 0) {
                return 1;
            }

            return $result;
        });
    }
}
