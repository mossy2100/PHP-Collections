<?php

declare(strict_types = 1);

namespace Galaxon\Collections;

use ArrayIterator;
use Countable;
use Galaxon\Core\Stringify;
use IteratorAggregate;
use Override;
use Stringable;
use Traversable;

/**
 * Base class for all collections in this package.
 *
 */
abstract class Collection implements Countable, IteratorAggregate, Stringable
{
    // region Properties

    /**
     * Array of items in the collection.
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Allowed types for values in this collection.
     *
     * @var TypeSet
     */
    protected(set) TypeSet $valueTypes;

    // endregion

    // region Constructor and factory methods

    /**
     * Constructor.
     *
     * @param string|iterable|null $types Optional value type constraint for collection items.
     */
    public function __construct(string|iterable|null $types = null)
    {
        // Convert provided types to a TypeSet object.
        $this->valueTypes = new TypeSet($types);
    }

    /**
     * Construct a new Collection from an iterable.
     * The value types will be inferred from the iterable's items.
     *
     * @param iterable $src The source collection.
     * @return static The new Collection.
     */
    abstract public static function fromIterable(iterable $src): static;

    // endregion

    // region Modification methods

    /**
     * Remove all items from the Collection.
     *
     * @return $this
     */
    public function clear(): static
    {
        // Remove all the items.
        $this->items = [];

        // Return $this for chaining.
        return $this;
    }

    // endregion

    // region Inspection methods

    /**
     * Checks if the Collection is empty.
     *
     * @return bool
     */
    public function empty(): bool
    {
        return empty($this->items);
    }

    /**
     * Check if the Collection contains a value.
     *
     * Strict equality is used, i.e. the item must match on both value and type.
     *
     * @param mixed $value The item to check for.
     * @return bool True if the Collection contains the item, false otherwise.
     */
    abstract public function contains(mixed $value): bool;

    /**
     * Check if all items in the Collection pass a test.
     *
     * NB: If calling this method on a Dictionary, the callback function must be able to accept a KeyValuePair.
     *
     * This method is analogous to array_all().
     * @see https://www.php.net/manual/en/function.array-all.php
     *
     * @param callable $fn The test function.
     * @return bool True if all items pass the test, false otherwise.
     */
    public function all(callable $fn): bool
    {
        return array_all($this->items, $fn);
    }

    /**
     * Check if any items in the Collection pass a test.
     *
     * NB: If calling this method on a Dictionary, the callback function must be able to accept a KeyValuePair.
     *
     * This method is analogous to array_any().
     * @see https://www.php.net/manual/en/function.array-any.php
     *
     * @param callable $fn The test function.
     * @return bool True if any items pass the test, false otherwise.
     */
    public function any(callable $fn): bool
    {
        return array_any($this->items, $fn);
    }

    /**
     * Check if two Collections have the same type and number of items.
     *
     * Protected helper method for use by equals() implementations.
     *
     * @param Collection $other The other Collection.
     * @return bool True if the Collections have the same type and number of items, false otherwise.
     */
    protected function eqTypeAndCount(Collection $other): bool
    {
        return $this::class === $other::class && count($this->items) === count($other->items);
    }

    /**
     * Check if the Collection is equal to another Collection.
     *
     * @param Collection $other The other Collection.
     * @return bool True if the Collections are equal, false otherwise.
     */
    abstract public function eq(Collection $other): bool;

    // endregion

    // region IteratorAggregate implementation

    /**
     * Get iterator for foreach loops.
     *
     * @return Traversable The iterator.
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    // endregion

    // region Countable implementation

    /**
     * Get the number of items in the Collection.
     *
     * @return int
     */
    #[Override]
    public function count(): int
    {
        return count($this->items);
    }

    // endregion

    // region Stringable implementation and other conversion methods

    /**
     * Convert the Collection to a string.
     *
     * @return string The string.
     */
    #[Override]
    public function __toString(): string
    {
        return Stringify::stringifyObject($this);
    }

    /**
     * Convert the Collection to an array of KeyValuePair objects.
     *
     * @return array The array.
     */
    public function toArray(): array
    {
        return $this->items;
    }

    // endregion
}
