<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Errors;

class JsonLogicException extends \RuntimeException
{
    private mixed $errorType;

    public function __construct(mixed $type = null)
    {
        parent::__construct((string)($type ?? ''));
        $this->errorType = $type;
    }

    public function getErrorType(): mixed
    {
        return $this->errorType;
    }

    public function payload(): array
    {
        return ['type' => $this->errorType];
    }
}
