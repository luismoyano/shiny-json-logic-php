<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\Engine;
use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Utils\Arr;
use ShinyJsonLogic\Errors\JsonLogicException as ErrorBase;

class TryOp extends AbstractOperation
{
    public static function call(mixed $rules, ScopeStack $scopeStack): mixed
    {
        // If rules is a single operation (not a list), wrap it so we treat it as one item
        if (is_array($rules) && Arr::isAssoc($rules)) {
            $items = [$rules];
        } else {
            $items = Arr::wrapNull($rules);
        }
        $lastError = null;

        foreach ($items as $item) {
            // If previous item was an error, push error payload as context
            if ($lastError !== null) {
                $scopeStack->push([]);               // intermediate level for [[1]] access
                $scopeStack->push($lastError->payload());
            }

            try {
                $result = Engine::evaluate($item, $scopeStack);

                if ($lastError !== null) {
                    $scopeStack->pop(); // error payload
                    $scopeStack->pop(); // intermediate level
                }

                return $result;
            } catch (ErrorBase $e) {
                if ($lastError !== null) {
                    $scopeStack->pop(); // error payload
                    $scopeStack->pop(); // intermediate level
                }
                $lastError = $e;
            }
        }

        if ($lastError !== null) {
            throw $lastError;
        }

        return null;
    }
}
