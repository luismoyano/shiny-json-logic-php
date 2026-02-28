<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Numericals\MinMaxCollection;

class Min extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $values = MinMaxCollection::collectNumericValues($rules, $scopeStack);
        return min($values);
    }
}
