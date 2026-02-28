<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Operations;

use ShinyJsonLogic\ScopeStack;
use ShinyJsonLogic\Numericals\Numerify;
use ShinyJsonLogic\Utils\Arr;
use ShinyJsonLogic\Utils\EmptyObject;

class Addition extends AbstractOperation
{
    protected static function execute(mixed $rules, ScopeStack $scopeStack): mixed
    {
        return static::safeArithmetic(function () use ($rules, $scopeStack) {
            // An empty object ({}) passed as operand list is invalid → NaN.
            // EmptyObject sentinel is set by resolveRules when json_decode stdclass mode sends {}.
            if ($rules instanceof EmptyObject) {
                static::handleNan();
            }
            // A non-empty assoc array (object with keys) passed directly as operand list → NaN.
            // Note: in arrays mode, empty {} and [] are both [] so {} cannot be detected there.
            if (is_array($rules) && !empty($rules) && Arr::isAssoc($rules)) {
                static::handleNan();
            }
            $result = 0.0;
            foreach (Arr::wrapNull($rules) as $rule) {
                $val = Numerify::numerify(static::evaluate($rule, $scopeStack));
                $result += ($val === null ? 0 : $val);
            }
            return $result;
        });
    }
}
