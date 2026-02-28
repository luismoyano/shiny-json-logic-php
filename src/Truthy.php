<?php

declare(strict_types=1);

namespace ShinyJsonLogic;

class Truthy
{
    public static function isTruthy(mixed $subject): bool
    {
        if ($subject === true || $subject === false) {
            return $subject;
        }
        if (is_int($subject) || is_float($subject)) {
            return $subject != 0;
        }
        if (is_string($subject)) {
            return $subject !== '';
        }
        if (is_array($subject)) {
            return !empty($subject);
        }
        if ($subject === null) {
            return false;
        }
        return true;
    }
}
