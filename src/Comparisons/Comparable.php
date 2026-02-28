<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Comparisons;

use ShinyJsonLogic\Engine;
use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Utils\Arr;
use ShinyJsonLogic\Numericals\Numerify;
use ShinyJsonLogic\Errors\InvalidArguments;
use ShinyJsonLogic\Errors\NotANumber;

class Comparable
{
    /**
     * Compare two values. Returns -1, 0, 1, or 'nan' on incompatible types.
     */
    public static function compare(mixed $a, mixed $b): mixed
    {
        if (is_array($a) || is_array($b)) {
            return 'nan';
        }

        if (is_string($a) && is_string($b)) {
            return $a <=> $b;
        }

        $numA = self::numerifyForComparison($a);
        $numB = self::numerifyForComparison($b);

        if ($numA === null || $numB === null) {
            return 'nan';
        }

        return $numA <=> $numB;
    }

    public static function numerifyForComparison(mixed $value): mixed
    {
        if (is_int($value) || is_float($value)) {
            return (float)$value;
        }
        if ($value === false) {
            return 0.0;
        }
        if ($value === true) {
            return 1.0;
        }
        if ($value === null) {
            return 0.0;
        }
        if (is_string($value) && Numerify::isNumericString($value)) {
            return (float)$value;
        }
        return null;
    }

    public static function cast(mixed $value): mixed
    {
        return (is_int($value) || is_float($value)) ? (float)$value : $value;
    }

    /**
     * Evaluate a chain of comparisons. Callback receives the spaceship result
     * and should return true to continue, false to short-circuit.
     */
    public static function compareChain(mixed $rules, ScopeStack $scopeStack, callable $callback): bool
    {
        $operands = Arr::wrapNull($rules);
        if (count($operands) < 2) {
            throw new InvalidArguments();
        }

        $prev = Engine::evaluate($operands[0], $scopeStack);
        $rest = array_slice($operands, 1);
        foreach ($rest as $rule) {
            $curr = Engine::evaluate($rule, $scopeStack);
            $result = self::compare($prev, $curr);
            if ($result === 'nan') {
                throw new NotANumber();
            }
            if (!$callback($result)) {
                return false;
            }
            $prev = $curr;
        }
        return true;
    }
}
