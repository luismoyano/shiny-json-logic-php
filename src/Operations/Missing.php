<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Utils\Arr;

class Missing extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $items = Arr::wrapNull($rules);
        $keys = [];
        foreach ($items as $rule) {
            $evaluated = static::evaluate($rule, $scopeStack);
            foreach (Arr::wrapNull($evaluated) as $v) {
                $keys[] = (string)$v;
            }
        }

        $currentData = $scopeStack->current();
        if (!is_array($currentData)) {
            return $keys;
        }

        $dataKeys = self::deepKeys($currentData);
        return array_values(array_diff($keys, $dataKeys));
    }

    protected static function deepKeys(array $hash): array
    {
        $result = [];
        foreach ($hash as $key => $value) {
            $subKeys = is_array($value) ? self::deepKeys($value) : [];
            if (empty($subKeys)) {
                $result[] = (string)$key;
            } else {
                foreach ($subKeys as $sub) {
                    $result[] = $key . '.' . $sub;
                }
            }
        }
        return $result;
    }
}
