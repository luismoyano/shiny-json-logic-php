<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations\Iterable;

use ShinyJsonLogic\Engine;
use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Truthy;
use ShinyJsonLogic\Utils\Arr;
use ShinyJsonLogic\Errors\InvalidArguments;
use ShinyJsonLogic\Operations\AbstractOperation;

abstract class IterableBase extends AbstractOperation
{
    protected static function throwOnNullFilter(): bool
    {
        return false;
    }

    public static function call(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $rules = static::resolveRules($rules, $scopeStack);
        [$collection, $filter] = static::setupCollection($rules, $scopeStack);

        static::onBefore($scopeStack);

        $results = [];
        foreach ($collection as $index => $item) {
            $scopeStack->push(['index' => $index], $index);
            $scopeStack->push($item, $index);
            try {
                $results[] = static::onEach($item, $filter, $scopeStack);
            } finally {
                $scopeStack->pop();
                $scopeStack->pop();
            }
        }

        return static::onAfter($results, $scopeStack);
    }

    protected static function setupCollection(mixed $rules, ScopeStack $scopeStack): array
    {
        if (!is_array($rules)) {
            static::handleNull();
        }

        $filter = $rules[1] ?? null;

        if ($filter === null && static::throwOnNullFilter()) {
            static::handleNull();
        }

        $collectionRule = !empty($rules) ? $rules[0] : $rules;
        if ($collectionRule === null) {
            static::handleNull();
        }

        $collection = Arr::wrap(Engine::evaluate($collectionRule, $scopeStack));
        return [$collection, $filter];
    }

    protected static function onBefore(ScopeStack $scopeStack): void
    {
    }

    protected static function onEach(mixed $item, mixed $filter, ScopeStack $scopeStack): mixed
    {
        return Engine::evaluate($filter, $scopeStack);
    }

    protected static function onAfter(array $results, ScopeStack $scopeStack): mixed
    {
        return $results;
    }

    protected static function handleNull(): never
    {
        throw new InvalidArguments();
    }
}
