<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Numericals\Numerify;
use ShinyJsonLogic\Utils\Arr;

class Modulo extends AbstractOperation
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
                $count++;
                // remainder (not fmod) — same sign as dividend
                $result = ($result === null) ? $num : fmod((float)$result, (float)$num);
            }

            if ($count < 2) {
                static::handleInvalidArgs();
            }

            return $result;
        });
    }
}
