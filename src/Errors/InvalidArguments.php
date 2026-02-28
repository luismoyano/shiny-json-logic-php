<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Errors;

class InvalidArguments extends JsonLogicException
{
    public function __construct()
    {
        parent::__construct('Invalid Arguments');
    }
}
