<?php

declare(strict_types=1);

namespace ShinyJsonLogic;

use ShinyJsonLogic\Utils\Arr;

class ScopeStack
{
    /** @var array<int, array{0: mixed, 1: int}> */
    private array $stack;

    public function __construct(mixed $rootData)
    {
        $this->stack = [[$rootData, 0]];
    }

    public function push(mixed $data, int $index = 0): void
    {
        $this->stack[] = [$data, $index];
    }

    public function pop(): void
    {
        if (count($this->stack) > 1) {
            array_pop($this->stack);
        }
    }

    public function current(): mixed
    {
        return $this->stack[count($this->stack) - 1][0];
    }

    public function resolve(int $levels, mixed ...$keys): mixed
    {
        $targetIndex = count($this->stack) - 1 - $levels;
        if ($targetIndex < 0) {
            return null;
        }
        $scope = $this->stack[$targetIndex] ?? null;
        if ($scope === null) {
            return null;
        }
        $data = $scope[0];
        if (empty($keys)) {
            return $data;
        }
        return $this->digValue($data, $keys);
    }

    private function digValue(mixed $data, array $keys): mixed
    {
        if ($data === null) {
            return null;
        }
        foreach ($keys as $key) {
            if ($data === null) {
                return null;
            }
            if (is_array($data) && Arr::isAssoc($data)) {
                $data = $data[(string)$key] ?? null;
            } elseif (is_array($data)) {
                $data = $data[(int)$key] ?? null;
            } else {
                return null;
            }
        }
        return $data;
    }
}
