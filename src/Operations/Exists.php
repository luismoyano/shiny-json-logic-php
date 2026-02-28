<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Utils\Arr;

class Exists extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        try {
            $current = $scopeStack->current();

            foreach (Arr::wrapNull($rules) as $rule) {
                $segment = static::evaluate($rule, $scopeStack);
                if (!is_array($current) || !array_key_exists((string)$segment, $current)) {
                    return false;
                }
                $current = $current[(string)$segment];
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
