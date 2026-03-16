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
            if (empty($subject)) {
                return false; // both [] and {} with no keys are falsy
            }
            // Non-empty JSON objects (assoc arrays) are always truthy, even if all values are falsy.
            // Non-empty JSON arrays are truthy.
            return true;
        }
        if ($subject === null) {
            return false;
        }
        if ($subject instanceof \stdClass) {
            // Empty object {} is falsy (mirrors JS: Boolean({}) is true, but JSONLogic treats it as falsy)
            return count((array)$subject) > 0;
        }
        return true;
    }
}
