<?php

namespace Iterare;

/**
 * Convert $iterable to an Iterator.
 *
 * @param iterable $iterable
 * @return \Iterator
 */
function toIterator($iterable): \Iterator {
    if ($iterable instanceof \Iterator) {
        return $iterable;
    }
    if ($iterable instanceof \IteratorAggregate) {
        return $iterable->getIterator();
    }
    if (\is_array($iterable)) {
        return new \RecursiveArrayIterator($iterable);
    }
    return new \ArrayIterator([$iterable]);
}

/**
 * Map all elements in $iterable to new elements
 *
 * @param iterable $iterable
 * @param callable $callback
 */
function map($iterable, $callback): \Iterator {
    return new MapIterator(toIterator($iterable), $callback);
}

/**
 * Get an iterable of all elements $callback returns true for
 *
 * @param iterable $iterable
 * @param callable $callback
 */
function filter($iterable, $callback): \Iterator {
    return new CallbackFilterIterator(toIterator($iterable), $callback);
}

/**
 * @param iterable $iterable
 * @param callable $callback Called with `($carry, $item, $key)` and returns the next `$carry`
 * @param mixed $initial The first `$carry`
 * @return mixed
 */
function reduce($iterable, $callback, $initial = null) {
    $carry = $inital;
    foreach ($iterable as $key => $item) {
        $carry = $callback($carry, $item, $key);
    }
    return $carry;
}

/**
 * Returns an Iterator that yields chunks of a string, delimited by $delimiter.
 * Example: Parsing CSV
 *
 * @param string $str
 * @param string $delimiter
 * @return \Iterator
 */
function explode(string $str, string $delimiter): \Iterator {
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        yield $i => $str[$i];
    }
}

/**
 * Implodes an iterable of values that can be casted to string to a single string
 *
 * @param iterable $iterable
 * @param string $glue
 * @return string
 */
function implode($iterable, string $glue = ''): string {
    $iterable = toIterator($iterable);
    $iterable->rewind();
    if (!$iterable->valid()) {
        return '';
    }
    $str = $iterable->current();
    while ($iterable->valid()) {
        $str .= $glue . $iterable->current();
    }
    return $str;
}

/**
 * Check whether $callback returns true for all elements
 *
 * @param iterable $iterable
 * @param callable $callback Called with `($value, $key)` and must return `bool`
 * @return bool
 */
function every($iterable, $callback): bool {
    foreach ($iterable as $key => $value) {
        if (!$callback($value, $key)) {
            return false;
        }
    }
    return true;
}

/**
 * Check whether $callback returns true for any element
 *
 * @param iterable $iterable
 * @param callable $callback Called with `($value, $key)` and must return `bool`
 * @return bool
 */
function some($iterable, $callback): bool {
    foreach ($iterable as $key => $value) {
        if ($callback($value, $key)) {
            return true;
        }
    }
    return false;
}

/**
 * Find the key of $needle in $iterable
 *
 * @param iterable $iterable
 * @param mixed $needle The value to search
 * @return mixed|false The iterator key of the needle or `false` if not found
 */
function search($iterable, $needle) {
    foreach ($iterable as $key => $value) {
        if ($value === $needle) {
            return $key;
        }
    }
    return false;
}

/**
 * Check whether $iterable contains $needle
 *
 * @param iterable $iterable
 * @param mixed $needle The needle to search
 * @return bool true if found, false otherwise
 */
function includes($iterable, $needle): bool {
    foreach ($iterable as $value) {
        if ($value === $needle) {
            return true;
        }
    }
    return false;
}

/**
 * Get only the elements between $offset and $count
 *
 * @param iterable $iterable
 * @param int $offset
 * @param int $count
 * @return \Iterable
 */
function slice($iterable, int $offset = 0, int $count = -1): \Iterable {
    return new \LimitIterator(toIterator($iterable), $offset, $count);
}

/**
 * Get only the $count first elements
 *
 * @param iterable $iterable
 * @param int $offset
 * @param int $count
 * @return \Iterable
 */
function take($iterable, int $count = -1): \Iterable {
    return new \LimitIterator(toIterator($iterable), 0, $count);
}

/**
 * Drop $count elements from the beginning
 *
 * @param iterable $iterable
 * @param int $offset
 * @param int $count
 * @return \Iterable
 */
function drop($iterable, int $count = -1): \Iterable {
    return new \LimitIterator(toIterator($iterable), $count);
}

/**
 * Get all but the first element
 *
 * @param iterable $iterable
 * @return \Iterable
 */
function tail($iterable): \Iterable {
    return new \LimitIterator(toIterator($iterable), 1);
}

/**
 * Get all but the last element
 *
 * @param iterable $iterable
 * @return \Iterable
 */
function initial($iterable): \Iterable {
    $iterable = toIterator($iterable);
    $iterable->rewind();
    if (!$iterable->valid()) {
        return;
    }
    $prev = $iterable->current();
    $iterable->next();
    while ($iterable->valid()) {
        yield $prev;
        $iterable->next();
    }
}

/**
 * Flatten a recursive iterable one level
 *
 * @param iterable $iterable
 * @return \RecursiveIterator
 */
function flatten($iterable): \RecursiveIterator {
    return flattenRecursive($iterable, $callback);
}

/**
 * Map every item to another iterable that is flattened
 *
 * @param iterable $iterable
 * @param callable $callback
 * @return \RecursiveIterator
 */
function flatMap($iterable, $callback): \RecursiveIterator {
    return flatten(map($iterable, $callback));
}

/**
 * Flattens a recursive iterable into a flat iterable
 *
 * @param iterable $iterable Array, RecursiveIterator or IteratorAggregate that returns a RecursiveIterator
 * @param int $mode See `\RecursiveIteratorIterator`
 * @return \RecursiveIterable
 */
function flattenRecursive($iterable, int $mode = \RecursiveIteratorIterator::LEAVES_ONLY, $depth = -1): \RecursiveIterator {
    $iterable = toIterable($iterable);
    if (!($iterable instanceof \RecursiveIterator)) {
        throw new \InvalidArgumentException('Iterable is not recursive');
    }
    $reIt = new \RecursiveIteratorIterator($iterable);
    $reIt->setMaxDepth($depth);
    return $reIt;
}

/**
 * Filters a recursive iterable, maintaining its recursive nature.
 * If the callback returns false, the whole subtree of that node will not be traversed.
 * The callback is never called for leaves.
 *
 * @param iterable $iterable Array, RecursiveIterator or IteratorAggregate that returns a RecursiveIterator
 * @param callable $callback Called with `($value, $key)`
 * @return \RecursiveIterable
 */
function filterRecursive($iterable, $callback): \RecursiveIterator {
    $iterable = toIterable($iterable);
    if (!($iterable instanceof \RecursiveIterator)) {
        throw new \InvalidArgumentException('Iterable is not recursive');
    }
    return new \RecursiveCallbackFilterIterator($iterable, function ($value, $key, $iterator) use ($callback) {
        return !$iterator->hasChildren() || $callback($value, $key);
    });
}

/**
 * Returns the first element of an iterable, or null
 *
 * @param iterable $iterable
 * @return mixed|null
 */
function head($iterable) {
    $iterable = toIterator($iterable);
    $iterable->rewind();
    if (!$iterable->valid()) {
        return null;
    }
    return $iterable->current();
}

/**
 * Returns the last element of an iterable, or null
 *
 * @param iterable $iterable
 * @return mixed|null
 */
function last($iterable) {
    $iterable = toIterator($iterable);
    $iterable->rewind();
    if (!$iterable->valid()) {
        return null;
    }
    // If Iterator allows it, skip to end
    if ($iterable instanceof \SeekableIterator && $iterable instanceof \Countable) {
        $iterable->seek($iterable->count() - 1);
        return $iterable->current();
    }
    while ($iterable->valid()) {
        $current = $iterable->current();
        $iterable->next();
    }
    return $current;
}

/**
 * Concatenates an iterable with another
 *
 * @param iterable $iterable
 * @param iterable $iterables
 * @return \Iterator
 */
function merge($iterable, ...$iterables): \Iterator {
    $appendIt = new \AppendIterator(toIterator($iterable));
    foreach ($iterables as $toAppend) {
        $appendIt->append(toIterator($toAppend));
    }
    return $appendIt;
}

/**
 * Returns an Iterator with flipped keys and values
 *
 * @param iterable $iterable
 * @return \Iterable
 */
function flip($iterable): \Iterable {
    foreach ($iterable as $key => $value) {
        yield $value => $key;
    }
}

/**
 * Returns the first element $callback returns true for
 *
 * @param iterable $iterable
 * @param callable $callback
 * @return mixed|null
 */
function find($iterable, $callback) {
    foreach ($iterable as $key => $value) {
        if ($callback($value, $key)) {
            return $value;
        }
    }
    return null;
}
