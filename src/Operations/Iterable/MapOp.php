<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations\Iterable;

class MapOp extends IterableBase
{
    protected static function throwOnNullFilter(): bool { return true; }
    protected static function throwOnDynamicArgs(): bool { return true; }
}
