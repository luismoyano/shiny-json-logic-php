<?php

declare(strict_types=1);

namespace ShinyJsonLogic\Utils;

/**
 * Marker class for already-resolved user data.
 * Engine uses instanceof check to skip operator parsing for these values.
 * PHP can't subclass arrays, so we wrap them and implement ArrayAccess.
 */
class DataArray implements \ArrayAccess, \Countable
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public static function wrap(mixed $obj): mixed
    {
        if (!is_array($obj)) {
            return $obj;
        }
        if ($obj instanceof self) {
            return $obj;
        }
        return new self($obj);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    public function count(): int
    {
        return count($this->data);
    }
}
