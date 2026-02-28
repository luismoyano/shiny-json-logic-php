<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations\Iterable;

use ShinyJsonLogic\Engine;
use ShinyJsonLogic\ScopeStack;

class Reduce extends IterableBase
{
    protected static function throwOnDynamicArgs(): bool { return true; }

    public static function call(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $rules = static::resolveRules($rules, $scopeStack);

        [$collection, $filter] = static::setupCollection($rules, $scopeStack);

        // Evaluate initial accumulator (third argument)
        $accumulator = Engine::evaluate($rules[2] ?? null, $scopeStack);

        foreach ($collection as $index => $item) {
            $scopeStack->push(['index' => $index], $index);
            $reduceScope = ['current' => $item, 'accumulator' => $accumulator];
            $scopeStack->push($reduceScope, $index);
            try {
                $accumulator = Engine::evaluate($filter, $scopeStack);
                $scopeStack->pop();
                $scopeStack->pop();
            } catch (\Throwable $e) {
                $scopeStack->pop();
                $scopeStack->pop();
                throw $e;
            }
        }

        return $accumulator;
    }
}
