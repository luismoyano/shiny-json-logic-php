<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations\Iterable;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Truthy;

class None extends IterableBase
{
    protected static function throwOnDynamicArgs(): bool { return true; }

    protected static function onAfter(array $results, ScopeStack $scopeStack): mixed
    {
        if (empty($results)) {
            return true;
        }
        foreach ($results as $res) {
            if (Truthy::isTruthy($res)) {
                return false;
            }
        }
        return true;
    }
}
