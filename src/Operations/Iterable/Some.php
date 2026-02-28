<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations\Iterable;

use ShinyJsonLogic\ScopeStack;

class Some extends IterableBase
{
    protected static function throwOnDynamicArgs(): bool { return true; }

    protected static function onAfter(array $results, ScopeStack $scopeStack): mixed
    {
        foreach ($results as $res) {
            if ($res === true) {
                return true;
            }
        }
        return false;
    }
}
