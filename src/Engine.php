<?php

declare(strict_types=1);

namespace ShinyJsonLogic;

use ShinyJsonLogic\Utils\Arr;
use ShinyJsonLogic\Utils\DataArray;
use ShinyJsonLogic\Errors\UnknownOperator;
use function is_array;
use function count;
use function array_key_first;

class Engine
{
    /** @var array<string, class-string> */
    private static array $operations = [];

    public static function setOperations(array $operations): void
    {
        self::$operations = $operations;
    }

    public static function evaluate(mixed $rule, ScopeStack $scopeStack): mixed
    {
        // Already-resolved user data — unwrap to plain array to prevent re-evaluation
        if ($rule instanceof DataArray) {
            return $rule->toArray();
        }

        if (is_array($rule) && !empty($rule) && Arr::isAssoc($rule)) {
            if (count($rule) > 1) {
                throw new UnknownOperator();
            }
            $operationKey = (string)array_key_first($rule);
            $args = $rule[$operationKey];

            if (!isset(self::$operations[$operationKey])) {
                throw new UnknownOperator();
            }

            /** @var class-string $class */
            $class = self::$operations[$operationKey];
            $result = $class::call($args, $scopeStack);
            // Unwrap DataArray returned by operations (e.g. VarOp) so callers always get plain values
            return ($result instanceof DataArray) ? $result->toArray() : $result;
        }

        if (is_array($rule)) {
            // Sequential (non-assoc) array — evaluate each element
            $result = [];
            foreach ($rule as $val) {
                $result[] = self::evaluate($val, $scopeStack);
            }
            return $result;
        }

        // Primitive — return as-is
        return $rule;
    }
}
