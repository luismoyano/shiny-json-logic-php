<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Numericals;

class Numerify
{
    private const NUMERIC_REGEX = '/\A[+-]?(?:\d+\.?\d*|\d*\.?\d+)(?:[eE][+-]?\d+)?\z/';

    public static function numerify(mixed $value): mixed
    {
        if (is_int($value) || is_float($value)) {
            return (float)$value;
        }
        if ($value === '') {
            return 0.0;
        }
        if (is_string($value) && self::isNumericString($value)) {
            return (float)$value;
        }
        if ($value === false) {
            return 0;
        }
        if ($value === true) {
            return 1;
        }
        if ($value === null) {
            return null;
        }

        throw new \TypeError('Cannot convert ' . gettype($value) . ' to a number');
    }

    public static function isNumericString(string $value): bool
    {
        return (bool)preg_match(self::NUMERIC_REGEX, $value);
    }
}
