<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\Engine;
use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Utils\Arr;

class Preserve extends AbstractOperation
{
    public static function call(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $collection = Arr::wrap($rules);

        $results = [];
        foreach ($collection as $item) {
            $results[] = Engine::evaluate($item, $scopeStack);
        }

        return count($results) === 1 ? $results[0] : $results;
    }
}
