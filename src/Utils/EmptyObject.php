<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Utils;

/**
 * Sentinel representing a JSON empty object ({}) decoded via json_decode without the assoc flag.
 *
 * In PHP, (array)new stdClass() === [] — an empty stdClass is indistinguishable from an empty
 * array after casting. This sentinel preserves the "was an empty object" information through
 * the evaluation pipeline so operations like + can produce NaN instead of treating it as [].
 */
final class EmptyObject
{
    private static ?self $instance = null;

    private function __construct() {}

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
