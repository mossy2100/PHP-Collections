<?php

declare(strict_types = 1);

namespace Galaxon\Collections;

// Interfaces
use ArrayAccess;
use Traversable;

// Attributes
use Override;

// Throwables
use ArgumentCountError;
use OutOfBoundsException;
use TypeError;

// Galaxon
use Galaxon\Math\Stringify;

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
     * @param string|iterable|null $key_types Allowed types for dictionary keys. Accepts a type string
     *     (e.g. 'int|string|null'), or an iterable of type names, or null for any.
     * @param string|iterable|null $value_types Allowed types for dictionary values. Accepts a type string
     *     (e.g. 'float|bool'), or an iterable of type names, or null for any.
     */
    public function __construct(string|iterable|null $key_types = null, string|iterable|null $value_types = null)
    {
        // Convert provided types to TypeSet objects.
        $this->keyTypes = new TypeSet($key_types);
        parent::__construct($value_types);
    }

    /**
     * Construct a new Dictionary from an existing collection.
     * The key and value types will be inferred from the collection's items.
     *
     * @param iterable $src The source collection.
     * @return self The new dictionary.
     */
    #[Override]
    public static function fromIterable(iterable $src): static
    {
        // Instantiate the Dictionary.
        $dict = new self();

        // Copy the values into the new dictionary.
        foreach ($src as $key => $value) {
            // Collect the key and value types from the source collection.
            $dict->keyTypes->addValueType($key);
            $dict->valueTypes->addValueType($value);

            // Leverage offsetSet() to generate the lookup key and the key-value pair.
            $dict[$key] = $value;
        }

        return $dict;
    }

    // endregion

    // region Inspection methods

    /**
     * Get all the keys as an array.
     */
    public function keys(): array
    {
        return array_map(static fn($item) => $item->key, $this->items);
    }

    /**
     * Get all the values as an array.
     */
    public function values(): array
    {
        return array_map(static fn($item) => $item->value, $this->items);
    }

    /**
     * Get all the key-value pairs as an array.
     */
    public function entries(): array
    {
        return array_values($this->items);
    }

    // endregion

    // region Methods for checking existence

    /**
     * Check if a key exists in the dictionary.
     *
     * @param mixed $key The key to check.
     * @return bool True if the key exists, false otherwise.
     */
    public function hasKey(mixed $key): bool
    {
        return array_key_exists(Type::getStringKey($key), $this->items);
    }

    /**
     * Check if a value exists in the dictionary.
     * This method can be slow with large dictionaries; consider caching results or generating a reverse lookup table.
     *
     * @param mixed $value The value to check.
     * @return bool True if the value exists, false otherwise.
     */
    public function hasValue(mixed $value): bool
    {
        return array_any($this->items, static fn($item) => $item->value === $value);
    }

    // endregion

    // region Sorting methods

    /**
     * Sort the Dictionary by a custom callback function.
     *
     * @param callable $fn The callback function. This should take two arguments (the values to compare) and return an
     * integer equal to -1 (less than), 0 (equal), or 1 (greater than).
     * @return $this The sorted Dictionary.
     */
    public function sort(callable $fn): self {
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
    public function sortByKey(): self {
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
    public function sortByValue(): self {
        $fn = fn($a, $b) => $a->value <=> $b->value;
        return $this->sort($fn);
    }

    // endregion

    // region Miscellaneous methods

    /**
     * Swaps keys with values.
     *
     * If there are two keys referencing the same values, then the resulting array will contain only one key-value pair,
     * with the key equal to the value, and the value equal to the last key encountered that referenced that value.
     * No exception will be thrown. This matches the behavior of array_flip().
     *
     * @return self
     */
    public function flip(): self {
        // Create a new dictionary to hold the result.
        $result = new self($this->valueTypes, $this->keyTypes);

        // Iterate over the items in the current dictionary.
        foreach ($this->items as $item) {
            // Add the flipped key-value pair to the result. Calls offsetSet().
            $result[$item->value] = $item->key;
        }

        // Return the result.
        return $result;
    }

    /**
     * Merge two dictionaries.
     *
     * If the same key exists in both, the second key-value pair (from $other) will be kept.
     * No exception will be thrown.
     * This is the same behaviour as array_merge().
     *
     * @param self $other The dictionary to merge with this dictionary.
     * @return self The new dictionary containing pairs from both source dictionaries.
     */
    public function merge(self $other): self {
        // Create a new dictionary with the combined type constraints.
        $key_types = new TypeSet($this->keyTypes)->add($other->keyTypes);
        $value_types = new TypeSet($this->valueTypes)->add($other->valueTypes);
        $result = new self($key_types, $value_types);

        // Copy pairs from this dictionary.
        foreach ($this->items as $string_key => $pair) {
            $result->items[$string_key] = clone $pair;
        }

        // Copy pairs from the other dictionary.
        foreach ($other->items as $string_key => $pair) {
            $result->items[$string_key] = clone $pair;
        }

        return $result;
    }

    /**
     * Filter a dictionary using a callback function. The resulting dictionary will have the same type constraints,
     * and will only contain the key-value pairs that the filter callback returns true for.
     *
     * The callback must accept two parameters, for the key and the value, and return a bool.
     * It can accept more than two parameters, but any additional parameters must be optional.
     * Also, the callback's parameter types should match the dictionary's allowed key and value types.
     *
     * @param callable $callback A callback function that accepts a key and a value, and returns a bool.
     * @return self A new dictionary with the kept key-value pairs.
     * @throws TypeError If the callback's parameter types don't match the dictionary's key and value types.
     * @throws ArgumentCountError If the callback accepts more than two non-optional parameters.
     * Note also that the callback could throw other kinds of exceptions, or they could throw a TypeError or
     * ArgumentCountError for some other reason.
     */
    public function filter(callable $callback): self
    {
        // Create a new dictionary with the same type constraints.
        $result = new self($this->keyTypes, $this->valueTypes);

        // Apply the filter with validation.
        foreach ($this->items as $item) {
            // See if we want to keep this pair.
            $keep = $callback($item->key, $item->value);

            // Validate the result of the callback.
            if (!is_bool($keep)) {
                throw new TypeError("The filter callback must return a bool, got " . get_debug_type($keep) . ".");
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
     * Set an item by key.
     *
     * If a key is in use, the corresponding key-value pair will be replaced.
     * If not, a new key-value pair will be added to the dictionary.
     * Both the key and value types will be checked, and if either is invalid, a TypeError will be thrown.
     *
     * NB: If no offset is specified (e.g. $dict[] = $value), the $offset parameter value will be null.
     * Thus, if this syntax is used, the key will be null, if null is an allowed key type.
     * If not, a TypeError will be thrown
     * This behavior means if multiple $dict[] = $value expressions are used, the effect will not be to append
     * multiple values to the dictionary, as with an ordinary PHP array.
     * Rather, it will be to keep setting the value for the null key.
     *
     * @param mixed $offset The key to set.
     * @param mixed $value The value to set.
     * @throws TypeError If the offset (key) or value has a disallowed type.
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Check the types are valid.
        $this->keyTypes->checkType($offset, 'key');
        $this->valueTypes->checkType($value, 'value');

        // Get the string version of this key.
        $string_key = Type::getStringKey($offset);

        // Store the key-value pair in the items array.
        $this->items[$string_key] = new KeyValuePair($offset, $value);
    }

    /**
     * Get the value of an item by key.
     *
     * @param mixed $offset The key to get.
     * @return mixed The value of the item.
     */
    #[Override]
    public function offsetGet(mixed $offset): mixed
    {
        // Get the string version of this key.
        $string_key = Type::getStringKey($offset);

        // Check key exists.
        if (!array_key_exists($string_key, $this->items)) {
            throw new OutOfBoundsException("Unknown key: " . Stringify::abbrev($offset) . ".");
        }

        // Get the corresponding value.
        return $this->items[$string_key]->value;
    }

    /**
     * Check if a given key exists in the dictionary.
     *
     * @param mixed $offset The key to check.
     * @return bool True if the key is in the dictionary, false otherwise.
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        return $this->hasKey($offset);
    }

    /**
     * Unset an item by key.
     *
     * @param mixed $offset The key to unset.
     * @return void
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        // Get the string version of this key.
        $string_key = Type::getStringKey($offset);

        // Check key exists.
        if (!array_key_exists($string_key, $this->items)) {
            throw new OutOfBoundsException("Unknown key: " . Stringify::abbrev($offset) . ".");
        }

        // Unset the array item.
        unset($this->items[$string_key]);
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
        // This loop ignores the internal string keys, and returns the keys and values from the KeyValuePairs.
        foreach ($this->items as $item) {
            yield $item->key => $item->value;
        }
    }

    // endregion

    // region Conversion methods

    /**
     * Convert the Dictionary to a Sequence of KeyValuePairs.
     *
     * @return Sequence The new Sequence.
     */
    public function toSequence(): Sequence {
        return Sequence::fromIterable($this->items);
    }

    /**
     * Convert the Dictionary to a Set. The resulting Dictionary will contain only unique KeyValuePairs from the
     * Sequence.
     *
     * @return Set The new Set.
     */
    public function toSet(): Set {
        return Set::fromIterable($this->items);
    }

    // endregion
}
