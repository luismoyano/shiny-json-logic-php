<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\Engine;
use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\OperatorSolver;
use ShinyJsonLogic\Utils\Arr;
use ShinyJsonLogic\Utils\EmptyObject;
use ShinyJsonLogic\Errors\InvalidArguments;
use ShinyJsonLogic\Errors\NotANumber;
use function is_float;
use function is_nan;
use function is_infinite;

abstract class AbstractOperation
{
    public static function call(mixed $rules, ScopeStack $scopeStack): mixed
    {
        $rules = static::resolveRules($rules, $scopeStack);
        return static::execute($rules, $scopeStack);
    }

    protected static function resolveRules(mixed $rules, ScopeStack $scopeStack): mixed
    {
        // Normalize stdClass → array so isOp can detect operations regardless of json_decode mode.
        // Special case: empty stdClass ({}) must become EmptyObject sentinel, not [], because
        // (array)new stdClass() === [] and we'd lose the "was an object" information.
        if (is_object($rules) && !($rules instanceof \ShinyJsonLogic\Utils\DataArray)) {
            $rules = (array)$rules === [] ? EmptyObject::instance() : (array)$rules;
        }
        if ($rules instanceof EmptyObject) {
            return $rules;
        }
        $dynamic = static::isOp($rules);
        if ($dynamic) {
            $rules = Engine::evaluate($rules, $scopeStack);
            if (static::throwOnDynamicArgs()) {
                throw new InvalidArguments();
            }
        }
        return $rules;
    }

    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        throw new \LogicException(static::class . ' must implement execute()');
    }

    protected static function throwOnDynamicArgs(): bool
    {
        return false;
    }

    protected static function evaluate(mixed $rule, ScopeStack $scopeStack): mixed
    {
        return Engine::evaluate($rule, $scopeStack);
    }

    protected static function isOp(mixed $value): bool
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }
        if (!Arr::isAssoc($value)) {
            return false; // sequential array
        }
        return OperatorSolver::isOperation($value);
    }

    protected static function handleInvalidArgs(): never
    {
        throw new InvalidArguments();
    }

    protected static function handleNan(): never
    {
        throw new NotANumber();
    }

    protected static function safeArithmetic(callable $block): mixed
    {
        try {
            $result = $block();
            if (is_float($result) && (is_nan($result) || is_infinite($result))) {
                static::handleNan();
            }
            return $result;
        } catch (\TypeError | \DivisionByZeroError $e) {
            static::handleNan();
        }
    }

    protected static function wrapNull(mixed $obj): array
    {
        return Arr::wrapNull($obj);
    }

    protected static function wrap(mixed $obj): array
    {
        return Arr::wrap($obj);
    }
}
