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

    // region Instance methods

    /**
     * Checks if the Collection is empty.
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

    // region Membership query methods

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
     * Check if the Collection contains one or more values.
     *
     * Strict equality is used to compare values, i.e. the item must match on both value and type.
     *
     * @param mixed ...$values The values to check for.
     * @return bool True if the Collection contains all the values, false otherwise.
     */
    public function containsAll(mixed ...$values): bool
    {
        return array_all($values, fn($value) => $this->contains($value));
    }

    /**
     * Check if the Collection contains any of the given values.
     *
     * Strict equality is used to compare values, i.e. the item must match on both value and type.
     *
     * @param mixed ...$values The values to check for.
     * @return bool True if the Collection contains any of the values, false otherwise.
     */
    public function containsAny(mixed ...$values): bool
    {
        return array_any($values, fn($value) => $this->contains($value));
    }

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
    public function __toString(): string {
        return Stringify::stringifyObject($this);
    }

    /**
     * Convert the Collection to a normal PHP array.
     *
     * @return array The array.
     */
    public function toArray(): array {
        return $this->items;
    }

    // endregion
}
