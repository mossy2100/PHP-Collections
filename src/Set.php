<?php

declare(strict_types = 1);

namespace Galaxon\Collections;

/**
 * Implements a set of values with optional type constraints.
 * It is equivalent to Set<T> in Java or C#, except multiple types can be specified.
 */
final class Set extends Collection
{
    // region Constructor and factory methods

    /**
     * Construct a new Set by copying values and their types from a source iterable.
     *
     * @param iterable $src The iterable to copy from.
     * @return static The new Set.
     */
    public static function fromIterable(iterable $src): static
    {
        // Construct the new Set.
        $set = new self();

        // Add types from the source iterable.
        foreach ($src as $item) {
            // Add the item type to the allowed types.
            $set->valueTypes->addValueType($item);

            // Add the item to the Set.
            $set->add($item);
        }

        return $set;
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
     */
    public function add(mixed ...$items): self
    {
        // Add each item.
        foreach ($items as $item) {
            // Check if the item is allowed in the set.
            $this->valueTypes->checkType($item);

            // Add the item if new.
            $key = Type::getStringKey($item);
            if (!array_key_exists($key, $this->items)) {
                $this->items[$key] = $item;
            }
        }

        // Return $this for chaining.
        return $this;
    }

    /**
     * Remove one or more items from the Set.
     *
     * NB: This is a mutating method.
     *
     * @param mixed ...$items The items to remove from the Set, if present.
     * @return $this The modified Set.
     */
    public function remove(mixed ...$items): self
    {
        // Remove each item.
        foreach ($items as $item) {
            // No type check needed. If it's in the set, remove it.
            $key = Type::getStringKey($item);
            if (array_key_exists($key, $this->items)) {
                unset($this->items[$key]);
            }
        }

        // Return $this for chaining.
        return $this;
    }

    // endregion

    // region Inspection methods

    /**
     * Check if the Set contains one or more items.
     *
     * Strict equality is used to compare items, i.e. the item must match on both value and type.
     *
     * @param mixed ...$items The items to check for.
     * @return bool True if the Set contains all the items, false otherwise.
     */
    public function contains(mixed ...$items): bool
    {
        // Check each item.
        return array_all($items, fn($item) => array_key_exists(Type::getStringKey($item), $this->items));
    }

    /**
     * Check if the set contains any of the given items.
     *
     * @param mixed ...$items The items to check for.
     * @return bool If the set contains any of the items.
     */
    public function containsAny(mixed ...$items): bool
    {
        return array_any($items, fn($it) => $this->contains($it));
    }

    /**
     * Check if the set contains none of the given items.
     *
     * @param mixed ...$items The items to check for.
     * @return bool If the set contains none of the items.
     */
    public function containsNone(mixed ...$items): bool
    {
        return !$this->containsAny(...$items);
    }

    // endregion

    // region Set operations
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

    // region Comparison methods

    /**
     * Checks if two sets are equal, i.e. containing the same elements.
     *
     * The order of the elements in each Set is irrelevant.
     * The type constraints for each Set are also not considered.
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return ($this->count() === $other->count()) && $this->subset($other);
    }

    /**
     * Checks if a set is a subset of another set.
     *
     * @param self $other The set to compare with.
     * @return bool If $this is a subset of $other.
     */
    public function subset(self $other): bool
    {
        return array_all($this->items, static fn($item) => $other->contains($item));
    }

    /**
     * Checks if a set is a proper subset of another set.
     *
     * @param self $other The set to compare with.
     * @return bool If $this is a proper subset of $other.
     */
    public function properSubset(self $other): bool
    {
        return ($this->count() < $other->count()) && $this->subset($other);
    }

    /**
     * Checks if a set is a superset of another set.
     *
     * @param self $other The set to compare with.
     * @return bool If $this is a superset of $other.
     */
    public function superset(self $other): bool
    {
        return $other->subset($this);
    }

    /**
     * Checks if a set is a proper superset of another set.
     *
     * @param self $other The set to compare with.
     * @return bool If $this is a proper superset of $other.
     */
    public function properSuperset(self $other): bool
    {
        return $other->properSubset($this);
    }

    /**
     * Checks if two sets are disjoint, i.e. they have no elements in common.
     *
     * @param self $other The set to compare with.
     * @return bool True if the sets are disjoint; false otherwise.
     */
    public function disjoint(self $other): bool
    {
        return array_all($this->items, static fn($item) => !$other->contains($item));
    }

    // endregion

    // region Stringable implementation

    /**
     * Generate a string representation of the Set.
     *
     * @return string
     */
    public function __toString(): string
    {
        return '{' . implode(', ', array_map(static fn($item) => (string)$item, $this->items)) . '}';
    }

    // endregion

    // region Conversion methods

    /**
     * Convert the Set to a Dictionary.
     *
     * The Set's internal array indexes, unimportant within the context of a Set, will become keys in the new
     * Dictionary. The values aren't sorted.
     *
     * @return Dictionary The new Dictionary.
     */
    public function toDictionary(): Dictionary
    {
        // Construct the new Dictionary.
        $dict = new Dictionary('int', $this->valueTypes);

        // Copy the items into the new Dictionary.
        foreach ($this->items as $key => $value) {
            $dict[$key] = $value;
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
        return Sequence::fromIterable($this);
    }

    // endregion
}
