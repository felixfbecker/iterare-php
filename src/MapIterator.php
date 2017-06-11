<?php

namespace Iterare;

/**
 * Maps values before yielding
 */
class MapIterator extends \IteratorIterator
{
    /** @var callable */
    protected $callback;

    /**
     * @param Traversable $iterator Iterable
     * @param callable $callback Callback used for iterating
     *
     * @throws InvalidArgumentException if the callback if not callable
     */
    public function __construct(\Traversable $iterable, $callback)
    {
        parent::__construct($iterable);
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('The callback must be callable');
        }
        $this->callback = $callback;
    }

    public function current()
    {
        return call_user_func($this->callback, parent::current());
    }
}