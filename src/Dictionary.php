<?php

declare(strict_types=1);

namespace Galaxon\Collections;

use ArgumentCountError;
use ArrayAccess;
use Galaxon\Core\Stringify;
use Galaxon\Core\Types;
use OutOfBoundsException;
use Override;
use Traversable;
use TypeError;
use ValueError;

/**
 * Dictionary class that permits keys and values of any type, including scalar, complex, nullable,
 * and union types.
 *
 * @example
 * $customers = new Dictionary('int', 'Customer');
 * $sales_data = new Dictionary('DateTime', 'float');
 * $country_codes = new Dictionary('string', 'string');
 * $car_make = new Dictionary('string', '?string');
 */
final class Dictionary extends Collection implements ArrayAccess
{
    // region Properties

    /**
     * Allowed types for keys in this collection.
     *
     * @var TypeSet
     */
    protected(set) TypeSet $keyTypes;

    // endregion

    // region Constructor and factory methods

    /**
     * Constructor.
     *
     *  Allowed types can be specified in several ways:
     *  - null = Values of any type are allowed.
     *  - string = A type name, or multiple types using union or nullable type syntax, e.g. 'string', 'int|null', '?int'
     *  - iterable = Array or other collection of type names, e.g. ['string', 'int']
     *  - true = The types will be inferred from the source iterable's values.
     *
     * If a source iterable is provided, the Dictionary will be initialized with key-value pairs from the iterable.
     *
     * @param null|string|iterable|true $key_types Allowed types for keys (default true, for infer).
     * @param null|string|iterable|true $value_types Allowed types for values (default true, for infer).
     * @param iterable $source A source iterable to import key-value pairs from (optional).
     * @throws ValueError If a type name is invalid.
     * @throws TypeError If a type name is not specified as a string, or any imported keys/values have disallowed types.
     */
    public function __construct(
        null|string|iterable|true $key_types = true,
        null|string|iterable|true $value_types = true,
        iterable $source = []
    ) {
        // Determine if we should infer types from the source iterable.
        $infer_keys = $key_types === true;
        $infer_values = $value_types === true;

        // Instantiate the object and typesets.
        parent::__construct($infer_values ? null : $value_types);
        $this->keyTypes = new TypeSet($infer_keys ? null : $key_types);

        // Import initial key-value pairs from the source iterable.
        foreach ($source as $key => $value) {
            // Infer types from the source iterable if requested.
            if ($infer_keys) {
                $this->keyTypes->addValueType($key);
            }
            if ($infer_values) {
                $this->valueTypes->addValueType($value);
            }

            // Add item to the new Dictionary.
            $this[$key] = $value;
        }
    }

    /**
     * Create a new Dictionary by combining separate iterables of keys and values.
     *
     * By default, the key and value types will be automatically inferred from the provided iterables.
     * Otherwise, if $infer_types is false, the key and value typesets will both be null (i.e. any types allowed).
     *
     * @param iterable $keys The keys for the Dictionary.
     * @param iterable $values The values for the Dictionary.
     * @param bool $infer_types Whether to infer the key and value types (default true).
     * @return self A new Dictionary with the combined keys and values.
     * @throws ValueError If the iterables have different counts or if keys are not unique.
     */
    public static function combine(iterable $keys, iterable $values, bool $infer_types = true): self
    {
        // Convert to arrays to check counts, and enable access keys and values using a common numerical index.
        $keys_array = is_array($keys) ? array_values($keys) : iterator_to_array($keys, false);
        $values_array = is_array($values) ? array_values($values) : iterator_to_array($values, false);

        // Check counts match.
        $key_count = count($keys_array);
        $value_count = count($values_array);
        if ($key_count !== $value_count) {
            throw new ValueError("Cannot combine: keys count ($key_count) does not match values count ($value_count).");
        }

        // Create a new Dictionary and add items, checking for duplicate keys along the way.
        $dict = new self();
        for ($i = 0; $i < $key_count; $i++) {
            // Get the key.
            $key = $keys_array[$i];

            // Check for duplicate keys.
            if (isset($dict[$key])) {
                throw new ValueError("Cannot combine: keys are not unique.");
            }

            // Get the value.
            $value = $values_array[$i];

            // Infer types if requested.
            if ($infer_types) {
                $dict->keyTypes->addValueType($key);
                $dict->valueTypes->addValueType($value);
            }

            // Add the key-value pair to the Dictionary.
            $dict[$key] = $value;
        }

        // Return the Dictionary.
        return $dict;
    }

    // endregion

    // region Private helper methods

    /**
     * Validate an key (a.k.a. offset) argument.
     *
     * @param mixed $key The key to validate.
     * @return string The key as a corresponding index string.
     * @throws TypeError If the key has a disallowed type.
     * @throws OutOfBoundsException If the key does not exist in the Dictionary.
     */
    private function checkKey(mixed $key): string
    {
        // Check the key type is valid.
        $this->keyTypes->check($key, 'key');

        // Convert the key to an index.
        $index = Types::getUniqueString($key);

        // Check index (and thus key) exists in the Dictionary.
        if (!array_key_exists($index, $this->items)) {
            throw new OutOfBoundsException("Unknown key: " . Stringify::abbrev($key) . ".");
        }

        return $index;
    }

    // endregion

    // region Extraction methods

    /**
     * Get all the keys as an array.
     */
    public function keys(): array
    {
        return array_values(array_map(static fn($item) => $item->key, $this->items));
    }

    /**
     * Get all the values as an array.
     */
    public function values(): array
    {
        return array_values(array_map(static fn($item) => $item->value, $this->items));
    }

    // endregion

    // region Methods for adding and removing items

    /**
     * Add a key-value pair to the dictionary.
     *
     * This method can be called with two parameters, the key and the value, or one parameter only, a KeyValuePair.
     *
     * @param mixed $key_or_pair The key (two-param form), or a KeyValuePair (one-param form).
     * @param mixed $value The value (two-param form), or null (one-param form).
     * @return $this The modified Dictionary.
     * @throws TypeError If the key or value has a disallowed type.
     * @throws ArgumentCountError If the wrong number of parameters is supplied.
     * @throws TypeError If the one-param form is used and the argument is not a valid KeyValuePair.
     */
    public function add(mixed $key_or_pair, mixed $value = null): self
    {
        // Support calling the method with one parameter only (a KeyValuePair).
        $n_args = func_num_args();
        if ($n_args === 1) {
            if ($key_or_pair instanceof KeyValuePair) {
                $key = $key_or_pair->key;
                $value = $key_or_pair->value;
            } else {
                throw new TypeError("Invalid key-value pair: " . Stringify::abbrev($key_or_pair));
            }
        } elseif ($n_args === 2) {
            $key = $key_or_pair;
        } else {
            throw new ArgumentCountError("The add() method takes 1 or 2 parameters, got $n_args.");
        }

        // Check the types are valid.
        $this->keyTypes->check($key, 'key');
        $this->valueTypes->check($value, 'value');

        // Leverage offsetSet() to generate the lookup key and the key-value pair.
        $this[$key] = $value;

        // Return this for chaining.
        return $this;
    }

    /**
     * Import key-value pairs from an iterable into the Dictionary.
     *
     * @param iterable $src The source iterable.
     * @return $this The calling object.
     * @throws TypeError If any of the keys or values have a disallowed type.
     */
    #[Override]
    public function import(iterable $src): static
    {
        // Copy the source keys and values into the new dictionary.
        foreach ($src as $key => $value) {
            // Leverage offsetSet() to generate the lookup key and the key-value pair.
            $this[$key] = $value;
        }

        return $this;
    }

    /**
     * Remove an item by key.
     *
     * @param mixed $key The key to remove.
     * @return mixed The value of the removed item.
     * @throws TypeError If the key has a disallowed type.
     * @throws OutOfBoundsException If the Dictionary does not contain the given key.
     */
    public function removeByKey(mixed $key): mixed
    {
        // Validate the key.
        $index = $this->checkKey($key);

        // Get the corresponding value.
        $value = $this->items[$index]->value;

        // Remove the item denoted by the given key.
        $this->offsetUnset($key);

        // Return the value of the removed item.
        return $value;
    }

    /**
     * Remove one or more items by value.
     *
     * @param mixed $value The value to remove.
     * @return int The number of items removed.
     * @throws TypeError If the value has a disallowed type.
     */
    public function removeByValue(mixed $value): int
    {
        // Check the value type is valid.
        $this->valueTypes->check($value, 'value');

        // Initialize the counter.
        $n_removed = 0;

        // Remove all items with the given value.
        foreach ($this->items as $index => $pair) {
            if ($pair->value === $value) {
                unset($this->items[$index]);
                $n_removed++;
            }
        }

        // Return the number of items removed.
        return $n_removed;
    }

    // endregion

    // region Comparison and inspection methods
    // These are non-mutating and return bools.

    /**
     * Check if the Dictionary contains a value.
     *
     * This method can be slow with large dictionaries; consider caching results or generating a reverse lookup table.
     *
     * @param mixed $value The value to check for.
     * @return bool True if the value exists, false otherwise.
     */
    #[Override]
    public function contains(mixed $value): bool
    {
        return array_any($this->items, static fn($item) => $item->value === $value);
    }

    /**
     * Check if the Dictionary is equal to another Collection.
     *
     * "Equal" in this case means that the Dictionaries have the same:
     * - type (i.e. they are both instances of Dictionary)
     * - number of items (i.e. key-value pairs)
     * - item keys (strict equality)
     * - item values (strict equality)
     * - order of items
     *
     * Type constraints are not considered, because these are only relevant when adding items to a Dictionary.
     * Therefore, if the first Dictionary only permits 'int' values whereas the second permits both 'int' and 'string',
     * the two will still compare as equal if the other conditions are met.
     *
     * @param Collection $other The other Dictionary.
     * @return bool True if the Dictionaries are equal, false otherwise.
     */
    #[Override]
    public function equals(Collection $other): bool
    {
        // Check type and item count are equal.
        if (!$other instanceof self || count($this->items) !== count($other->items)) {
            return false;
        }

        // Check keys and item order are equal.
        // This actually compares the indexes (i.e. internal string keys), but that's equivalent to comparing the keys
        // in the KeyValuePairs. The !== operator compares type, value, and order of items.
        $this_keys = array_keys($this->items);
        $other_keys = array_keys($other->items);
        if ($this_keys !== $other_keys) {
            return false;
        }

        // Check values are equal.
        $eq = fn($pair, $index) => $this->items[$index]->value === $other->items[$index]->value;
        return array_all($this->items, $eq);
    }

    /**
     * Check if the Dictionary contains a key.
     *
     * This is an alias for offsetExists(), which isn't normally called directly.
     * This method has a better name, as the documentation uses "key" rather than "offset".
     *
     * @param mixed $key The key to check for.
     * @return bool True if the Dictionary contains the key, false otherwise.
     */
    public function keyExists(mixed $key): bool
    {
        return $this->offsetExists($key);
    }

    // endregion

    // region Sorting methods

    /**
     * Sort the Dictionary by a custom callback function.
     *
     * @param callable $fn The callback function. This should take two arguments (the key-value pairs to compare) and
     * return an integer equal to -1 (less than), 0 (equal), or 1 (greater than).
     * @return $this The sorted Dictionary.
     */
    public function sort(callable $fn): self
    {
        uasort($this->items, $fn);
        return $this;
    }

    /**
     * Sort the Dictionary by key.
     *
     * The spaceship operator (<=>) is used to compare the keys, so it should be defined for the key types.
     *
     * @return $this The sorted Dictionary.
     */
    public function sortByKey(): self
    {
        $fn = fn($a, $b) => $a->key <=> $b->key;
        return $this->sort($fn);
    }

    /**
     * Sort the Dictionary by value.
     *
     * The spaceship operator (<=>) is used to compare the values, so it should be defined for the value types.
     *
     * @return $this The sorted Dictionary.
     */
    public function sortByValue(): self
    {
        $fn = fn($a, $b) => $a->value <=> $b->value;
        return $this->sort($fn);
    }

    // endregion

    // region Miscellaneous methods

    /**
     * Swaps keys with values.
     *
     * All values in the Dictionary must be unique for flip to succeed.
     *
     * @return self A new Dictionary with keys and values swapped.
     * @throws ValueError If the Dictionary contains duplicate values.
     */
    public function flip(): self
    {
        // Create a new dictionary to hold the result. Swap the typesets.
        $result = new self($this->valueTypes, $this->keyTypes);

        // Iterate over the items in the current dictionary.
        foreach ($this->items as $item) {
            // Check if this value already exists as a key in the result.
            if ($result->keyExists($item->value)) {
                throw new ValueError("Cannot flip Dictionary: values are not unique.");
            }

            // Add the flipped key-value pair to the result. Calls offsetSet().
            $result[$item->value] = $item->key;
        }

        // Return the result.
        return $result;
    }

    /**
     * Merge two Dictionaries.
     *
     * If the same key exists in both, the second key-value pair (from $other) will be kept and no exception will be
     * thrown. This is the same behaviour as array_merge().
     *
     * @param self $other The Dictionary to merge with this Dictionary.
     * @return self The new Dictionary containing pairs from both source Dictionaries.
     */
    public function merge(self $other): self
    {
        // Create a new dictionary with the combined type constraints.
        $key_types = new TypeSet($this->keyTypes)->add($other->keyTypes);
        $value_types = new TypeSet($this->valueTypes)->add($other->valueTypes);
        $result = new self($key_types, $value_types);

        // Copy pairs from this dictionary.
        foreach ($this->items as $index => $pair) {
            $result->items[$index] = clone $pair;
        }

        // Copy pairs from the other dictionary.
        foreach ($other->items as $index => $pair) {
            $result->items[$index] = clone $pair;
        }

        return $result;
    }

    /**
     * Filter a Dictionary using a callback function.
     *
     * The resulting Dictionary will have the same type constraints, and will only contain the key-value pairs that
     * the filter callback returns true for.
     *
     * The callback must accept two parameters, for the key and the value, and return a bool.
     * It can accept more than two parameters, but any additional parameters must be optional.
     * Also, the callback's parameter types should match the dictionary's allowed key and value types.
     *
     * @param callable $callback A callback function that accepts a key and a value, and returns a bool.
     * @return self A new dictionary with the kept key-value pairs.
     * @throws TypeError If the callback's parameter types don't match the dictionary's key and value types.
     * Note also that the callback could throw other kinds of exceptions, or they could throw a TypeError for some
     * other reason.
     */
    #[Override]
    public function filter(callable $callback): static
    {
        // Create a new dictionary with the same type constraints.
        $result = new self($this->keyTypes, $this->valueTypes);

        // Apply the filter with validation.
        foreach ($this->items as $item) {
            // See if we want to keep this pair.
            $keep = $callback($item->key, $item->value);

            // Validate the result of the callback.
            if (!is_bool($keep)) {
                throw new TypeError("The filter callback must return a bool, got " . Types::getBasicType($keep) . ".");
            }

            // Add pair to keep to the result dictionary.
            if ($keep) {
                $result[$item->key] = $item->value;
            }
        }

        return $result;
    }

    // endregion

    // region ArrayAccess implementation

    /**
     * Check if a given key exists in the dictionary.
     *
     * @param mixed $offset The key to check.
     * @return bool True if the key is in the dictionary, false otherwise.
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        // Convert the key to an index.
        $index = Types::getUniqueString($offset);

        // Check index exists.
        return array_key_exists($index, $this->items);
    }

    /**
     * Get the value of an item by key.
     *
     * @param mixed $offset The key to get.
     * @return mixed The value of the item.
     * @throws TypeError If the offset (key) has a disallowed type.
     * @throws OutOfBoundsException If the Dictionary does not contain the given key.
     */
    #[Override]
    public function offsetGet(mixed $offset): mixed
    {
        // Validate the key.
        $index = $this->checkKey($offset);

        // Get the corresponding value.
        return $this->items[$index]->value;
    }

    /**
     * Set an item by key.
     *
     * If a key is in use, the corresponding key-value pair will be replaced.
     * If not, a new key-value pair will be added to the dictionary.
     * Both the key and value types will be checked, and if either is invalid, a TypeError will be thrown.
     *
     * NB: If no offset is specified (e.g. $dict[] = $value), the $offset parameter value will be null.
     * There's no way to know if the offset was not provided (i.e. $dict[]) or was null (i.e. $dict[null]).
     * Thus, if no offset is given, the Dictionary key is taken to be null, if null is an allowed key type.
     * If not, a TypeError will be thrown.
     * This behavior means if multiple $dict[] = $value expressions are used, the effect will not be to append
     * multiple values to the dictionary, as with an ordinary PHP array.
     * Rather, it will keep setting the value for the null key.
     *
     * @param mixed $offset The key to set.
     * @param mixed $value The value to set.
     * @throws TypeError If the offset (key) or value has a disallowed type.
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Check the types are valid.
        $this->keyTypes->check($offset, 'key');
        $this->valueTypes->check($value, 'value');

        // Convert the key to an index.
        $index = Types::getUniqueString($offset);

        // Store the key-value pair in the items array.
        $this->items[$index] = new KeyValuePair($offset, $value);
    }

    /**
     * Unset an item by key.
     *
     * @param mixed $offset The key to unset.
     * @return void
     * @throws TypeError If the offset (key) has a disallowed type.
     * @throws OutOfBoundsException If the Dictionary does not contain the given key.
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        // Validate the key.
        $index = $this->checkKey($offset);

        // Unset the array item.
        unset($this->items[$index]);
    }

    // endregion

    // region Conversion methods

    /**
     * Convert the Dictionary to a Sequence of KeyValuePairs.
     *
     * @return Sequence The new Sequence.
     */
    public function toSequence(): Sequence
    {
        return new Sequence(source: $this->items);
    }

    // endregion

    // region IteratorAggregate implementation

    /**
     * Get iterator for foreach loops.
     *
     * @return Traversable
     */
    #[Override]
    public function getIterator(): Traversable
    {
        // This loop ignores the indexes, and returns the keys and values from the KeyValuePairs.
        foreach ($this->items as $item) {
            yield $item->key => $item->value;
        }
    }

    // endregion
}
