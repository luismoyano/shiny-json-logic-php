<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations\Iterable;

use ShinyJsonLogic\Engine;
use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Truthy;

class Filter extends IterableBase
{
    protected static function throwOnNullFilter(): bool { return true; }
    protected static function throwOnDynamicArgs(): bool { return true; }

    protected static function onEach(mixed $item, mixed $filter, ScopeStack $scopeStack): mixed
    {
        return Truthy::isTruthy(Engine::evaluate($filter, $scopeStack)) ? $item : null;
    }

    protected static function onAfter(array $results, ScopeStack $scopeStack): mixed
    {
        return array_values(array_filter($results, fn($v) => $v !== null));
    }
}
