<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Utils\Arr;
use ShinyJsonLogic\Utils\DataArray;

class VarOp extends AbstractOperation
{
    public static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $items = Arr::wrapNull($rules);
        try {
            $key = isset($items[0]) ? static::evaluate($items[0], $scopeStack) : null;
            $default = isset($items[1]) ? static::evaluate($items[1], $scopeStack) : null;
            $currentData = $scopeStack->current();

            if ($key === null || $key === '') {
                return DataArray::wrap($currentData);
            }

            $result = self::fetchValue($currentData, $key);
            $result = ($result === null) ? $default : $result;
            return DataArray::wrap($result);
        } catch (\Throwable $e) {
            $default = isset($items[1]) ? static::evaluate($items[1], $scopeStack) : null;
            return $default ?? $scopeStack->current();
        }
    }

    private static function fetchValue(mixed $obj, mixed $key): mixed
    {
        if ($obj === null) {
            return null;
        }
        $keys = explode('.', (string)$key);
        $current = $obj;
        foreach ($keys as $k) {
            if ($current === null) {
                return null;
            }
            if (is_array($current) && Arr::isAssoc($current)) {
                $current = $current[$k] ?? null;
            } elseif (is_array($current)) {
                $current = $current[(int)$k] ?? null;
            } else {
                return null;
            }
        }
        return $current;
    }
}
