<?php

declare(strict_types=1);

namespace ShinyJsonLogic;

use ShinyJsonLogic\Utils\DataArray;
use ShinyJsonLogic\Utils\Arr;
use function is_array;
use function array_map;

class ShinyJsonLogic
{
    private static bool $initialized = false;

    private static function initialize(): void
    {
        if (!self::$initialized) {
            Engine::setOperations(OperatorSolver::solvers());
            self::$initialized = true;
        }
    }

    public static function apply(mixed $rule, mixed $data = []): mixed
    {
        self::initialize();
        $normalizedData = self::deepStringifyKeys($data ?? []);
        $scopeStack = new ScopeStack($normalizedData);
        return Engine::evaluate($rule, $scopeStack);
    }

    public static function deepStringifyKeys(mixed $obj): mixed
    {
        if (is_object($obj)) {
            $obj = (array)$obj;
        }
        if (is_array($obj) && Arr::isAssoc($obj)) {
            $result = [];
            foreach ($obj as $key => $value) {
                $result[(string)$key] = self::deepStringifyKeys($value);
            }
            return $result;
        }
        if (is_array($obj)) {
            return array_map([self::class, 'deepStringifyKeys'], $obj);
        }
        return $obj;
    }
}

// Aliases
if (!class_exists('JsonLogic', false)) {
    class_alias(ShinyJsonLogic::class, 'JsonLogic');
}
if (!class_exists('JSONLogic', false)) {
    class_alias(ShinyJsonLogic::class, 'JSONLogic');
}
