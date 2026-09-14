<?php

namespace App\Collections;

use InvalidArgumentException;

abstract class BaseTypedCollection extends \Illuminate\Support\Collection
{
    public function __construct($items = [])
    {
        parent::__construct($items);

        foreach ($this->items as $key => $item) {
            $this->assertItem($item, $key);
        }
    }

    public function add($item)
    {
        $this->assertItem($item);

        return parent::add($item);
    }

    public function push(...$values)
    {
        foreach ($values as $value) {
            $this->assertItem($value);
        }

        return parent::push(...$values);
    }

    public function prepend($value, $key = null)
    {
        $this->assertItem($value, $key);

        return parent::prepend(...func_get_args());
    }

    public function offsetSet($key, $value): void
    {
        $this->assertItem($value, $key);

        parent::offsetSet($key, $value);
    }

    protected function assertItem(mixed $item, int|string|null $key = null): void
    {
        $typedClassName = $this->getTypedClassName();
        if ($item instanceof $typedClassName) {
            return;
        }

        $position = $key !== null
            ? sprintf(' at key [%s]', $key)
            : '';

        throw new InvalidArgumentException(sprintf(
            'Expected instance of %s%s, %s given.',
            $typedClassName,
            $position,
            get_debug_type($item),
        ));
    }

    abstract protected function getTypedClassName(): string;
}
