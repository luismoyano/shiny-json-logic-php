<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Comparisons\Comparable;

class Different extends AbstractOperation
{
    protected static function throwOnDynamicArgs(): bool { return true; }

    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        return Comparable::compareChain($rules, $scopeStack, fn($r) => $r !== 0);
    }
}
