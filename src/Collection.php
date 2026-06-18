<?php

declare(strict_types=1);

namespace OceanMoon\Collections;

use Countable;
use DomainException;
use InvalidArgumentException;
use IteratorAggregate;
use OceanMoon\Core\Stringify;
use OceanMoon\Core\Traits\Comparison\Equatable;
use Override;
use Stringable;
use Traversable;

/**
 * Base class for all collections in this package.
 *
 * @implements IteratorAggregate<mixed, mixed>
 */
abstract class Collection implements Countable, IteratorAggregate, Stringable
{
    use Equatable;

    // region Properties

    /**
     * Array of items in the collection.
     *
     * @var array<array-key, mixed>
     */
    protected array $items = [];

    /**
     * Allowed types for values in this collection.
     *
     * @var TypeSet
     */
    protected(set) TypeSet $valueTypes;

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param null|string|iterable<string> $types Optional value type constraint for collection items.
     * @throws InvalidArgumentException If a type is not specified as a string.
     * @throws DomainException If a type name is invalid.
     */
    public function __construct(null|string|iterable $types = null)
    {
        // Convert provided types to a TypeSet object.
        $this->valueTypes = new TypeSet($types);
    }

    // endregion

    // region Modification methods

    /**
     * Import values from a source iterable into the Collection.
     *
     * @param iterable<mixed> $source A source iterable.
     * @return $this The calling object.
     * @throws InvalidArgumentException If any of the values have a disallowed type.
     */
    abstract public function import(iterable $source): static;

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
     * @return bool True if the Collection has no items, false otherwise.
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
     * NB: If calling this method on a Dictionary, the callback function must be able to accept a Pair object.
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
     * NB: If calling this method on a Dictionary, the callback function must be able to accept a Pair object.
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

    // endregion

    // region Transformation methods

    /**
     * Filter a Collection using a callback function.
     *
     * The result will have the same type constraints, and will only contain the values (or key-value pairs) that the
     * filter callback returns true for.
     *
     * The callback should only accept one parameter, the item, and return a bool.
     * It can accept more than one parameter, but any additional parameters must be optional.
     * The callback's parameter types should match or be wider than the Collection's type constraints.
     *
     * @param callable $callback A callback function that accepts an item and returns a bool.
     * @return static A new Collection with the kept items.
     */
    abstract public function filter(callable $callback): static;

    // endregion

    // region Conversion methods

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
     * Convert the Collection to an array.
     *
     * @return list<mixed> The array.
     */
    public function toArray(): array
    {
        return array_values($this->items);
    }

    // endregion

    // region IteratorAggregate implementation

    /**
     * Get iterator for foreach loops.
     *
     * @return Traversable<mixed> The iterator.
     */
    #[Override]
    abstract public function getIterator(): Traversable;

    // endregion

    // region Countable methods

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
}
