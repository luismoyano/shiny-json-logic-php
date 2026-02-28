<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Utils\DataArray;
use ShinyJsonLogic\Errors\JsonLogicException as ErrorBase;

class ThrowOp extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        // Unwrap DataArray to a plain PHP array so array_key_exists works
        $unwrapped = ($rules instanceof DataArray) ? $rules->toArray() : $rules;

        $rawValue = is_array($unwrapped) ? ($unwrapped[0] ?? $unwrapped) : $unwrapped;

        // If raw value is an operation, evaluate it
        if (static::isOp($rawValue)) {
            $errorType = static::evaluate($rawValue, $scopeStack);
            if ($errorType instanceof DataArray) {
                $errorType = $errorType->toArray();
            }
        } else {
            $errorType = $rawValue;
            if ($errorType instanceof DataArray) {
                $errorType = $errorType->toArray();
            }
        }

        // Extract type from {type: ...} hash
        if (is_array($errorType) && array_key_exists('type', $errorType)) {
            $errorType = $errorType['type'];
        }

        // Fall back to current scope's type
        if ($errorType === null) {
            $current = $scopeStack->current();
            if (is_array($current) && array_key_exists('type', $current)) {
                $errorType = $current['type'];
            }
        }

        throw new ErrorBase($errorType);
    }
}
