<?php

declare(strict_types = 1);

namespace Galaxon\Collections;

// Interfaces
use Countable;
use IteratorAggregate;
use Stringable;
use Traversable;

// Attributes
use Override;

// Throwables
use TypeError;

// Other
use ArrayIterator;

// Galaxon
use Galaxon\Math\Stringify;

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

    // region Countable implementation and isEmpty() method

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

    /**
     * Checks if the Set is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

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

    // region Stringable implementation and other conversion methods

    /**
     * Convert the Collection to a string.
     *
     * @return string The string.
     */
    #[Override]
    public function __toString(): string {
        return Stringify::stringifyObject($this);
    }

    /**
     * Convert the collection to a normal PHP array.
     *
     * @return array The array.
     */
    public function toArray(): array {
        return $this->items;
    }

    // endregion
}
