<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Utils;

class Arr
{
    public static function wrap(mixed $object): array
    {
        if ($object === null) {
            return [];
        }
        if (is_array($object)) {
            return $object;
        }
        return [$object];
    }

    public static function wrapNull(mixed $object): array
    {
        if ($object === null) {
            return [null];
        }
        return self::wrap($object);
    }

    public static function isAssoc(array $arr): bool
    {
        if (empty($arr)) {
            return true;
        }
        return !array_is_list($arr);
    }
}
