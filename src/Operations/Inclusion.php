<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;

class Inclusion extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        if (!is_array($rules) || count($rules) < 2) {
            return false;
        }

        $needle = static::evaluate($rules[0], $scopeStack);
        $haystack = static::evaluate($rules[count($rules) - 1], $scopeStack);

        if (is_array($haystack)) {
            return in_array($needle, $haystack, false);
        }
        if (is_string($haystack)) {
            return str_contains($haystack, (string)$needle);
        }
        return false;
    }
}
