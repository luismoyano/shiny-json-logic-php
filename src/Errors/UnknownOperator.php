<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Errors;

class UnknownOperator extends JsonLogicException
{
    public function __construct()
    {
        parent::__construct('Unknown Operator');
    }
}
