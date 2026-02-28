<?php

declare(strict_types=1);

namespace ShinyJsonLogic;

use ShinyJsonLogic\Operations\VarOp;
use ShinyJsonLogic\Operations\Val;
use ShinyJsonLogic\Operations\IfOp;
use ShinyJsonLogic\Operations\AndOp;
use ShinyJsonLogic\Operations\OrOp;
use ShinyJsonLogic\Operations\NotOp;
use ShinyJsonLogic\Operations\DoubleNot;
use ShinyJsonLogic\Operations\Equal;
use ShinyJsonLogic\Operations\StrictEqual;
use ShinyJsonLogic\Operations\Different;
use ShinyJsonLogic\Operations\StrictDifferent;
use ShinyJsonLogic\Operations\Greater;
use ShinyJsonLogic\Operations\GreaterEqual;
use ShinyJsonLogic\Operations\Smaller;
use ShinyJsonLogic\Operations\SmallerEqual;
use ShinyJsonLogic\Operations\Addition;
use ShinyJsonLogic\Operations\Subtraction;
use ShinyJsonLogic\Operations\Product;
use ShinyJsonLogic\Operations\Division;
use ShinyJsonLogic\Operations\Modulo;
use ShinyJsonLogic\Operations\Min;
use ShinyJsonLogic\Operations\Max;
use ShinyJsonLogic\Operations\Missing;
use ShinyJsonLogic\Operations\MissingSome;
use ShinyJsonLogic\Operations\Concatenation;
use ShinyJsonLogic\Operations\Substring;
use ShinyJsonLogic\Operations\Merge;
use ShinyJsonLogic\Operations\Inclusion;
use ShinyJsonLogic\Operations\Coalesce;
use ShinyJsonLogic\Operations\Exists;
use ShinyJsonLogic\Operations\Preserve;
use ShinyJsonLogic\Operations\ThrowOp;
use ShinyJsonLogic\Operations\TryOp;
use ShinyJsonLogic\Operations\Iterable\MapOp;
use ShinyJsonLogic\Operations\Iterable\Filter;
use ShinyJsonLogic\Operations\Iterable\Reduce;
use ShinyJsonLogic\Operations\Iterable\All;
use ShinyJsonLogic\Operations\Iterable\Some;
use ShinyJsonLogic\Operations\Iterable\None;

class OperatorSolver
{
    /** @var array<string, class-string> */
    private const SOLVERS = [
        'var'          => VarOp::class,
        'missing'      => Missing::class,
        'missing_some' => MissingSome::class,
        '=='           => Equal::class,
        '==='          => StrictEqual::class,
        '!='           => Different::class,
        '!=='          => StrictDifferent::class,
        '>'            => Greater::class,
        '>='           => GreaterEqual::class,
        '<'            => Smaller::class,
        '<='           => SmallerEqual::class,
        '!'            => NotOp::class,
        '!!'           => DoubleNot::class,
        'or'           => OrOp::class,
        'and'          => AndOp::class,
        'in'           => Inclusion::class,
        'cat'          => Concatenation::class,
        '%'            => Modulo::class,
        'max'          => Max::class,
        'min'          => Min::class,
        '+'            => Addition::class,
        '*'            => Product::class,
        '-'            => Subtraction::class,
        '/'            => Division::class,
        'substr'       => Substring::class,
        'merge'        => Merge::class,
        'val'          => Val::class,
        '??'           => Coalesce::class,
        'exists'       => Exists::class,
        'throw'        => ThrowOp::class,
        'try'          => TryOp::class,
        'if'           => IfOp::class,
        '?:'           => IfOp::class,
        'filter'       => Filter::class,
        'map'          => MapOp::class,
        'reduce'       => Reduce::class,
        'all'          => All::class,
        'none'         => None::class,
        'some'         => Some::class,
        'preserve'     => Preserve::class,
    ];

    public static function solvers(): array
    {
        return self::SOLVERS;
    }

    public static function isOperation(array $value): bool
    {
        foreach (array_keys($value) as $key) {
            if (array_key_exists((string)$key, self::SOLVERS)) {
                return true;
            }
        }
        return false;
    }
}
