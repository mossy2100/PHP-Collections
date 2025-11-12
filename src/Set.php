<?php

declare(strict_types = 1);

namespace Galaxon\Collections;

use ArrayIterator;
use Galaxon\Core\Stringify;
use Galaxon\Core\Types;
use Override;
use Traversable;
use TypeError;
use ValueError;

/**
 * Implements a set of values with optional type constraints.
 * It is equivalent to Set<T> in Java or C#, except multiple types can be specified.
 */
final class Set extends Collection
{
    // region Constructor and factory methods

    /**
     * Constructor.
     *
     * The allowed types for values in the Set can be specified in several ways:
     * - null = Values of any type are allowed.
     * - string = A type name, or multiple types using union type or nullable type syntax, e.g. 'string', 'int|null', '?int'
     * - iterable = Array or other collection of type names, e.g. ['string', 'int']
     * - true = The types will be inferred from the source iterable's values.
     *
     * If a source iterable is provided, the Set will be initialized with values from the iterable.
     *
     * @param null|string|iterable|true $types Optional type constraint for members (default true, for infer).
     * @param iterable $source A source iterable to import values from (optional).
     * @throws ValueError If a type name is invalid.
     * @throws TypeError If a type is not specified as a string, or any imported values have disallowed types.
     */
    public function __construct(null|string|iterable|true $types = true, iterable $source = [])
    {
        // Determine if we should infer the types from the source iterable.
        $infer = $types === true;

        // Instantiate the object and typeset.
        parent::__construct($infer ? null : $types);

        foreach ($source as $item) {
            // Collect types from the source iterable if requested.
            if ($infer) {
                $this->valueTypes->addValueType($item);
            }

            // Add item to the new Set.
            $this->add($item);
        }
    }

    // endregion

    // region Methods for adding and removing members

    /**
     * Add one or more items to the Set.
     *
     * NB: This is a mutating method.
     *
     * @param mixed ...$items The items to add to the Set.
     * @return $this The modified Set.
     * @throws TypeError If any of the items have an invalid type.
     */
    public function add(mixed ...$items): self
    {
        // Add each item.
        foreach ($items as $item) {
            // Check if the item is allowed in the set.
            $this->valueTypes->check($item);

            // Add the item if new.
            $index = Types::getUniqueString($item);
            if (!array_key_exists($index, $this->items)) {
                $this->items[$index] = $item;
            }
        }

        // Return $this for chaining.
        return $this;
    }

    /**
     * Import values from an iterable into the Set.
     *
     * NB: This is a mutating method.
     *
     * @param iterable $src The source iterable.
     * @return $this The calling object.
     * @throws TypeError If any of the values have a disallowed type.
     */
    #[Override]
    public function import(iterable $src): static
    {
        // Copy items from the source iterable into the Sequence.
        $this->add(...$src);

        // Return this for chaining.
        return $this;
    }

    /**
     * Remove an item from the Set.
     *
     * NB: This is a mutating method.
     *
     * @param mixed $item The item to remove from the Set, if present.
     * @return bool True if an item was removed, false otherwise.
     */
    public function remove(mixed $item): bool
    {
        // Check if the item is in the set.
        $index = Types::getUniqueString($item);
        if (array_key_exists($index, $this->items)) {
            // Remove the item.
            unset($this->items[$index]);
            return true;
        }

        return false;
    }

    // endregion

    // region Classic set operations
    // These are non-mutating and return new sets.

    /**
     * Return the union of this set and another set.
     * The resulting set will allow the types allowed by both sets.
     *
     * @param self $other The set to union with.
     * @return self A new set equal to the union of the two sets.
     */
    public function union(self $other): self
    {
        // Construct the new set.
        // The types for the result Set should include types from both input sets.
        // In theory, these should be the same (why would you want a union of two sets with different types?),
        // but it's no trouble to allow for the possibility.
        $result = new self($this->valueTypes);
        $result->valueTypes->add($other->valueTypes);

        // Get the items. We can use the union operator because the same items will have the same keys.
        $result->items = $this->items + $other->items;

        return $result;
    }

    /**
     * Return the intersection of this set and another set.
     * The resulting set will allow the same types as the $this set.
     *
     * @param self $other The set to intersect with.
     * @return self A new set equal to the intersection of the two sets.
     */
    public function intersect(self $other): self
    {
        // Construct the new set using the types from the calling object.
        $result = new self($this->valueTypes);

        // Add items present in both sets.
        foreach ($this->items as $k => $v) {
            if (array_key_exists($k, $other->items)) {
                $result->items[$k] = $v;
            }
        }

        // Return the new set.
        return $result;
    }

    /**
     * Return the difference of this set and another set.
     * The resulting set will allow the same types as the $this set.
     *
     * @param self $other The set to subtract from.
     * @return self A new set equal to the difference of the two sets.
     */
    public function diff(self $other): self
    {
        // Construct the new set using the types from the calling object.
        $result = new self($this->valueTypes);

        // Add items present in this set that are not present in the other set.
        foreach ($this->items as $k => $v) {
            if (!array_key_exists($k, $other->items)) {
                $result->items[$k] = $v;
            }
        }

        // Return the new set.
        return $result;
    }

    // endregion

    // region Comparison and inspection methods
    // These are non-mutating and return bools.

    /**
     * Check if the Set contains one or more items.
     *
     * Strict equality is used to compare items, i.e. the item must match on both value and type.
     *
     * @param mixed $value The items to check for.
     * @return bool True if the Set contains the item, false otherwise.
     */
    #[Override]
    public function contains(mixed $value): bool
    {
        return array_key_exists(Types::getUniqueString($value), $this->items);
    }

    /**
     * Check if the Set is equal to another Collection.
     *
     * "Equal" in this case means that the Sets have the same:
     * - type (i.e. they are both instances of Set)
     * - number of items
     * - item values (strict equality)
     *
     * Type constraints are not considered, because these are only relevant when adding values to a Set.
     * Therefore, if the first Set only permits 'int' values whereas the second permits both 'int' and 'string',
     * the two will still compare as equal if the other conditions are met.
     *
     * The order of the items is also not considered, because with Sets the order doesn't matter.
     *
     * @param Collection $other The other Set.
     * @return bool True if the Sets are equal, false otherwise.
     */
    #[Override]
    public function equals(Collection $other): bool
    {
        // Check type and item count are equal.
        if (!$this->equalTypeAndCount($other)) {
            return false;
        }

        // Check values are equal. Order doesn't matter, so we can call isSubsetOf().
        return $this->isSubsetOf($other);
    }

    /**
     * Checks if this set is a subset of another set.
     *
     * @param self $other The set to compare with.
     * @return bool True if $this is a subset of $other, false otherwise.
     */
    public function isSubsetOf(self $other): bool
    {
        return array_all($this->items, static fn($item) => $other->contains($item));
    }

    /**
     * Checks if this set is a proper subset of another set.
     *
     * @param self $other The set to compare with.
     * @return bool True if $this is a proper subset of $other, false otherwise.
     */
    public function isProperSubsetOf(self $other): bool
    {
        return ($this->count() < $other->count()) && $this->isSubsetOf($other);
    }

    /**
     * Checks if this set is a superset of another set.
     *
     * @param self $other The set to compare with.
     * @return bool True if $this is a superset of $other, false otherwise.
     */
    public function isSupersetOf(self $other): bool
    {
        return $other->isSubsetOf($this);
    }

    /**
     * Checks if this set is a proper superset of another set.
     *
     * @param self $other The set to compare with.
     * @return bool True if $this is a proper superset of $other, false otherwise.
     */
    public function isProperSupersetOf(self $other): bool
    {
        return $other->isProperSubsetOf($this);
    }

    /**
     * Checks if this set is disjoint from another set, i.e. they have no elements in common.
     *
     * @param self $other The set to compare with.
     * @return bool True if this set is disjoint from the other set, false otherwise.
     */
    public function isDisjointFrom(self $other): bool
    {
        return array_all($this->items, static fn($item) => !$other->contains($item));
    }

    // endregion

    // region Collection methods

    /**
     * Filter a Set using a callback function.
     *
     * The result will have the same type constraints, and will only contain the values that the filter callback returns
     * true for.
     *
     * The callback must accept one parameter, for the value, and return a bool.
     * The parameter type should match or be wider than the Set's allowed value types.
     * It can accept more than one parameter, but any additional parameters must be optional.
     *
     * @param callable $callback A callback function that accepts a value and returns a bool.
     * @return self A new Set with the kept values.
     * @throws TypeError If the callback's parameter types don't match the dictionary's key and value types.
     * Note also that the callback could throw other kinds of exceptions, or they could throw a TypeError for some
     * other reason.
     */
    #[Override]
    public function filter(callable $callback): static
    {
        // Create a new Set with the same type constraints.
        $result = new self($this->valueTypes);

        // Apply the filter with validation.
        foreach ($this->items as $item) {
            // See if we want to keep this item.
            $keep = $callback($item);

            // Validate the result of the callback.
            if (!is_bool($keep)) {
                throw new TypeError("The filter callback must return a bool, got " . Types::getBasicType($keep) . ".");
            }

            // Add item to the result Set.
            if ($keep) {
                $result->add($item);
            }
        }

        return $result;
    }

    // endregion

    // region Stringable implementation

    /**
     * Generate a string representation of the Set.
     *
     * @return string
     * @throws ValueError If any values cannot be stringified.
     * @throws TypeError If any values have an unknown type.
     */
    public function __toString(): string
    {
        $items = array_map(static fn($item) => Stringify::stringify($item), $this->items);
        return '{' . implode(', ', $items) . '}';
    }

    // endregion

    // region Conversion methods

    /**
     * Convert the Set to a Dictionary.
     *
     * The keys will be sequential integers starting from 0.
     *
     * @return Dictionary The new Dictionary.
     */
    public function toDictionary(): Dictionary
    {
        // Construct the new Dictionary, using the same value types as the Set.
        $dict = new Dictionary('uint', $this->valueTypes);

        // Copy the items into the new Dictionary. Sequential unsigned integers are generated for the keys.
        $key = 0;
        foreach ($this->items as $value) {
            $dict->add($key, $value);
            $key++;
        }

        // Return the new Dictionary.
        return $dict;
    }

    /**
     * Convert the Set to a Sequence.
     *
     * @return Sequence The new Sequence.
     */
    public function toSequence(): Sequence
    {
        // Construct the new Sequence, using the same value types as the Set.
        $seq = new Sequence($this->valueTypes);

        // Copy the items into the new Sequence.
        foreach ($this->items as $value) {
            $seq->append($value);
        }

        // Return the new Sequence.
        return $seq;
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
        // This is more memory efficient than array_values() as it avoids an array copy.
        // If a foreach loop is called on this generator then sequential unsigned integer keys will be generated by
        // PHP automatically.
        foreach ($this->items as $value) {
            yield $value;
        }
    }

    // endregion
}
