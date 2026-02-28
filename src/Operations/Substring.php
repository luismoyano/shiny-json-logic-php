<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;

class Substring extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        if (!is_array($rules)) {
            return '';
        }

        $str = (string)static::evaluate($rules[0] ?? null, $scopeStack);
        $start = (int)static::evaluate($rules[1] ?? 0, $scopeStack);
        $hasLength = isset($rules[2]);
        $length = $hasLength ? (int)static::evaluate($rules[2], $scopeStack) : mb_strlen($str);

        $strLen = mb_strlen($str);

        if ($start < 0) {
            $start = $strLen + $start;
        }
        if ($start < 0) {
            $start = 0;
        }
        if ($start >= $strLen) {
            return '';
        }

        if ($length < 0) {
            $finish = $strLen + $length;
        } else {
            $finish = $start + $length;
        }

        if ($finish <= $start) {
            return '';
        }

        return mb_substr($str, $start, $finish - $start) ?: '';
    }
}
