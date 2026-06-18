<?php

declare(strict_types=1);

namespace OceanMoon\Collections;

use ArgumentCountError;
use ArrayAccess;
use DomainException;
use InvalidArgumentException;
use LengthException;
use OceanMoon\Core\Stringify;
use OceanMoon\Core\Types;
use OutOfBoundsException;
use Override;
use Traversable;
use UnexpectedValueException;

/**
 * Dictionary class that permits keys and values of any type, including scalar, complex, nullable,
 * and union types.
 *
 * @example
 * $customers = new Dictionary('int', 'Customer');
 * $salesData = new Dictionary('DateTime', 'float');
 * $countryCodes = new Dictionary('string', 'string');
 * $carMake = new Dictionary('string', '?string');
 *
 * @implements ArrayAccess<mixed, mixed>
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

    // region Property hooks

    /**
     * Get all the keys as an array.
     *
     * @var list<mixed>
     */
    public array $keys {
        get {
            $keys = [];
            foreach ($this->items as $pair) {
                /** @var Pair $pair */
                $keys[] = $pair->key;
            }
            return $keys;
        }
    }

    /**
     * Get all the values as an array.
     *
     * @var list<mixed>
     */
    public array $values {
        get {
            $values = [];
            foreach ($this->items as $pair) {
                /** @var Pair $pair */
                $values[] = $pair->value;
            }
            return $values;
        }
    }

    // endregion

    // region Constructor

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
     * @param null|string|iterable<string>|true $keyTypes Allowed key types (default true, for infer).
     * @param null|string|iterable<string>|true $valueTypes Allowed value types (default true, for infer).
     * @param iterable<mixed, mixed> $source A source iterable to import key-value pairs from (optional).
     * @throws DomainException If a type name is invalid.
     * @throws InvalidArgumentException If a type name is not specified as a string, or any imported keys/values have
     * disallowed types.
     */
    public function __construct(
        null|string|iterable|true $keyTypes = true,
        null|string|iterable|true $valueTypes = true,
        iterable $source = []
    ) {
        // Determine if we should infer types from the source iterable.
        $inferKeys = $keyTypes === true;
        $inferValues = $valueTypes === true;

        // Instantiate the object and typesets.
        parent::__construct($inferValues ? null : $valueTypes);
        $this->keyTypes = new TypeSet($inferKeys ? null : $keyTypes);

        // Import initial key-value pairs from the source iterable.
        foreach ($source as $key => $value) {
            // Infer types from the source iterable if requested.
            if ($inferKeys) {
                $this->keyTypes->addValueType($key);
            }
            if ($inferValues) {
                $this->valueTypes->addValueType($value);
            }

            // Add item to the new Dictionary.
            $this[$key] = $value;
        }
    }

    // endregion

    // region Factory methods

    /**
     * Create a new Dictionary by combining separate iterables of keys and values.
     *
     * By default, the key and value types will be automatically inferred from the provided iterables.
     * Otherwise, if $inferTypes is false, the key and value typesets will both be null (i.e. any types allowed).
     *
     * @param iterable<mixed> $keys The keys for the Dictionary.
     * @param iterable<mixed> $values The values for the Dictionary.
     * @param bool $inferTypes Whether to infer the key and value types (default true).
     * @return self A new Dictionary with the combined keys and values.
     * @throws LengthException If the iterables have different counts.
     * @throws OutOfBoundsException If the keys are not unique.
     */
    public static function combine(iterable $keys, iterable $values, bool $inferTypes = true): self
    {
        // Convert to arrays to check counts, and enable access keys and values using a common numerical index.
        $keysArray = is_array($keys) ? array_values($keys) : iterator_to_array($keys, false);
        $valuesArray = is_array($values) ? array_values($values) : iterator_to_array($values, false);

        // Check counts match.
        $keyCount = count($keysArray);
        $valueCount = count($valuesArray);
        if ($keyCount !== $valueCount) {
            throw new LengthException(
                "Cannot combine: keys count ($keyCount) does not match values count ($valueCount)."
            );
        }

        // Create a new Dictionary and add items, checking for duplicate keys along the way.
        $dict = new self();
        for ($i = 0; $i < $keyCount; $i++) {
            // Get the key.
            $key = $keysArray[$i];

            // Check for duplicate keys.
            if (isset($dict[$key])) {
                throw new OutOfBoundsException('Cannot combine: keys are not unique.');
            }

            // Get the value.
            $value = $valuesArray[$i];

            // Infer types if requested.
            if ($inferTypes) {
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

    // region Modification methods

    /**
     * Add a key-value pair to the dictionary.
     *
     * This method can be called with two parameters, the key and the value, or one parameter only, a Pair.
     *
     * @param mixed $keyOrPair The key (two-param form), or a Pair (one-param form).
     * @param mixed $value The value (two-param form), or null (one-param form).
     * @return $this The modified Dictionary.
     * @throws InvalidArgumentException If the one-param form is used, and the argument is not a Pair; or if the key
     * or value has a disallowed type.
     * @throws ArgumentCountError If the wrong number of parameters is supplied.
     */
    public function add(mixed $keyOrPair, mixed $value = null): self
    {
        // Support calling the method with one parameter only (a Pair).
        $nArgs = func_num_args();
        if ($nArgs === 1) {
            if (!$keyOrPair instanceof Pair) {
                throw new InvalidArgumentException(
                    'Cannot add: expected a Pair, got ' . get_debug_type($keyOrPair) . '.'
                );
            }
            $key = $keyOrPair->key;
            $value = $keyOrPair->value;
        } elseif ($nArgs === 2) {
            $key = $keyOrPair;
        } else {
            throw new ArgumentCountError("The add() method takes 1 or 2 parameters, got $nArgs.");
        }

        // Check the types are valid.
        $this->keyTypes->checkValueType($key, 'key');
        $this->valueTypes->checkValueType($value, 'value');

        // Leverage offsetSet() to generate the index and the pair.
        $this[$key] = $value;

        // Return this for chaining.
        return $this;
    }

    /**
     * Import key-value pairs from an iterable into the Dictionary.
     *
     * @param iterable<mixed, mixed> $source The source iterable.
     * @return $this The calling object.
     */
    #[Override]
    public function import(iterable $source): static
    {
        // Copy the source keys and values into the new dictionary.
        foreach ($source as $key => $value) {
            // Leverage offsetSet() to generate the index and the pair.
            $this[$key] = $value;
        }

        return $this;
    }

    /**
     * Remove an item by key.
     *
     * @param mixed $key The key to remove.
     * @return mixed The value of the removed item.
     * @throws InvalidArgumentException If the key has a disallowed type.
     * @throws OutOfBoundsException If the Dictionary does not contain the given key.
     */
    public function removeByKey(mixed $key): mixed
    {
        // Validate the key.
        $index = $this->checkKey($key);

        // Get the item.
        /** @var Pair $pair */
        $pair = $this->items[$index];

        // Get the corresponding value.
        $value = $pair->value;

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
     * @throws InvalidArgumentException If the value has a disallowed type.
     */
    public function removeByValue(mixed $value): int
    {
        // Check the value type is valid.
        $this->valueTypes->checkValueType($value, 'value');

        // Initialize the counter.
        $nRemoved = 0;

        // Remove all items with the given value.
        foreach ($this->items as $index => $pair) {
            /** @var Pair $pair */
            if ($pair->value === $value) {
                unset($this->items[$index]);
                $nRemoved++;
            }
        }

        // Return the number of items removed.
        return $nRemoved;
    }

    // endregion

    // region Inspection methods

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
        foreach ($this->items as $pair) {
            /** @var Pair $pair */
            if ($pair->value === $value) {
                return true;
            }
        }
        return false;
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

    // region Comparison methods

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
     * @param mixed $other The other Dictionary.
     * @return bool True if the Dictionaries are equal, false otherwise.
     */
    #[Override]
    public function equal(mixed $other): bool
    {
        // Check type and item count are equal.
        if (!$other instanceof self || count($this->items) !== count($other->items)) {
            return false;
        }

        // Check keys and item order are equal.
        // This actually compares the indexes (i.e. internal string keys), but that's equivalent to comparing the keys
        // in the Pairs. The !== operator compares type, value, and order of items.
        $thisIndexes = array_keys($this->items);
        $otherIndexes = array_keys($other->items);
        if ($thisIndexes !== $otherIndexes) {
            return false;
        }

        // Check values are equal.
        foreach ($thisIndexes as $index) {
            /** @var Pair $thisPair */
            $thisPair = $this->items[$index];
            /** @var Pair $otherPair */
            $otherPair = $other->items[$index];
            if ($thisPair->value !== $otherPair->value) {
                return false;
            }
        }
        return true;
    }

    // endregion

    // region Transformation methods

    /**
     * Filter a Dictionary using a callback function.
     *
     * The resulting Dictionary will have the same type constraints, and will only contain the key-value pairs that
     * the filter callback returns true for.
     *
     * The callback must accept one parameter, a Pair, and return a bool.
     *
     * @param callable $callback A callback function that accepts a Pair and returns a bool.
     * @return self A new dictionary with the kept key-value pairs.
     * @throws UnexpectedValueException If the callback doesn't return a bool.
     */
    #[Override]
    public function filter(callable $callback): static
    {
        // Create a new dictionary with the same type constraints.
        $result = new self($this->keyTypes, $this->valueTypes);

        // Apply the filter with validation.
        foreach ($this->items as $pair) {
            /** @var Pair $pair */

            // See if we want to keep this pair.
            $keep = $callback($pair);

            // Validate the result of the callback.
            if (!is_bool($keep)) {
                throw new UnexpectedValueException(
                    'Cannot filter: callback returned ' . Types::getBasicType($keep) . ', expected bool.'
                );
            }

            // Add pair to keep to the result dictionary.
            if ($keep) {
                $result[$pair->key] = $pair->value;
            }
        }

        return $result;
    }

    /**
     * Swaps keys with values.
     *
     * All values in the Dictionary must be unique for flip to succeed.
     *
     * @return self A new Dictionary with keys and values swapped.
     * @throws OutOfBoundsException If the Dictionary contains duplicate values.
     */
    public function flip(): self
    {
        // Create a new dictionary to hold the result. Swap the typesets.
        $result = new self($this->valueTypes, $this->keyTypes);

        // Iterate over the items in the current dictionary.
        foreach ($this->items as $pair) {
            /** @var Pair $pair */

            // Check if this value already exists as a key in the result.
            if ($result->keyExists($pair->value)) {
                throw new OutOfBoundsException('Cannot flip Dictionary: values are not unique.');
            }

            // Add the flipped key-value pair to the result. Calls offsetSet().
            $result[$pair->value] = $pair->key;
        }

        // Return the result.
        return $result;
    }

    /**
     * Applies a callback to transform each key-value pair in the Dictionary.
     *
     * The callback receives each Pair object and must return a new Pair.
     * Both keys and values can be transformed, and their types can change.
     * The result Dictionary will have its key and value types automatically inferred
     * from the callback results.
     *
     * The original Dictionary is not modified.
     *
     * @param callable(Pair): Pair $fn The callback function to apply to each item.
     * @return self A new Dictionary containing the transformed key-value pairs.
     * @throws UnexpectedValueException If the callback doesn't return a Pair.
     * @throws OutOfBoundsException If the callback produces duplicate keys.
     *
     * @example
     *   $dict = new Dictionary('string', 'int');
     *   $dict->add('apple', 5);
     *   $dict->add('banana', 3);
     *
     *   // Double all values.
     *   $doubled = $dict->map(fn($pair) => new Pair($pair->key, $pair->value * 2));
     *   // Result: ['apple' => 10, 'banana' => 6]
     *
     *   // Transform keys to uppercase.
     *   $upper = $dict->map(fn($pair) => new Pair(strtoupper($pair->key), $pair->value));
     *   // Result: ['APPLE' => 5, 'BANANA' => 3]
     *
     *   // Swap keys and values.
     *   $swapped = $dict->map(fn($pair) => new Pair($pair->value, $pair->key));
     *   // Result: [5 => 'apple', 3 => 'banana']
     */
    public function map(callable $fn): self
    {
        // Initialize the result Dictionary with no type constraints.
        $result = new self();

        /** @var Pair $pair */
        foreach ($this->items as $pair) {
            // Call the mapping function.
            $newPair = $fn($pair);

            // Validate the result is a Pair.
            if (!$newPair instanceof Pair) {
                throw new UnexpectedValueException(
                    'Cannot map: callback returned ' . Types::getBasicType($newPair) . ', expected Pair.'
                );
            }

            // Check for duplicate keys.
            if ($result->keyExists($newPair->key)) {
                throw new OutOfBoundsException(
                    'Callback produced a duplicate key: ' . Stringify::abbrev($newPair->key) . '.'
                );
            }

            // Add the types.
            $result->keyTypes->addValueType($newPair->key);
            $result->valueTypes->addValueType($newPair->value);

            // Add the pair to the result.
            $result->add($newPair);
        }

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
        $keyTypes = new TypeSet($this->keyTypes)->add($other->keyTypes);
        $valueTypes = new TypeSet($this->valueTypes)->add($other->valueTypes);
        $result = new self($keyTypes, $valueTypes);

        // Copy pairs from this dictionary.
        foreach ($this->items as $index => $pair) {
            /** @var Pair $pair */
            $result->items[$index] = clone $pair;
        }

        // Copy pairs from the other dictionary.
        foreach ($other->items as $index => $pair) {
            /** @var Pair $pair */
            $result->items[$index] = clone $pair;
        }

        return $result;
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
        $fn = static fn ($a, $b) => $a->key <=> $b->key;
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
        $fn = static fn ($a, $b) => $a->value <=> $b->value;
        return $this->sort($fn);
    }

    // endregion

    // region Conversion methods

    /**
     * Convert the Dictionary to a Sequence of Pairs.
     *
     * @return Sequence The new Sequence.
     */
    public function toSequence(): Sequence
    {
        return new Sequence(source: $this->items);
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
     * @throws InvalidArgumentException If the offset (key) has a disallowed type.
     * @throws OutOfBoundsException If the Dictionary does not contain the given key.
     */
    #[Override]
    public function offsetGet(mixed $offset): mixed
    {
        // Validate the key and get the index.
        $index = $this->checkKey($offset);

        // Get the key-value pair.
        /** @var Pair $pair */
        $pair = $this->items[$index];

        // Get the value.
        return $pair->value;
    }

    /**
     * Set an item by key.
     *
     * If a key is in use, the corresponding key-value pair will be replaced.
     * If not, a new key-value pair will be added to the dictionary.
     * Both the key and value types will be checked, and if either is invalid, a InvalidArgumentException will be
     * thrown.
     *
     * NB: If no offset is specified (e.g. $dict[] = $value), the $offset parameter value will be null.
     * There's no way to know if the offset was not provided (i.e. $dict[]) or was null (i.e. $dict[null]).
     * Thus, if no offset is given, the Dictionary key is taken to be null, if null is an allowed key type.
     * If not, a InvalidArgumentException will be thrown.
     * This behavior means if multiple $dict[] = $value expressions are used, the effect will not be to append
     * multiple values to the dictionary, as with an ordinary PHP array.
     * Rather, it will keep setting the value for the null key.
     * if this is a problem for users we could disallow null keys, but for now we'll allow them.
     *
     * @param mixed $offset The key to set.
     * @param mixed $value The value to set.
     * @throws InvalidArgumentException If the offset (key) or value has a disallowed type.
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Check the types are valid.
        $this->keyTypes->checkValueType($offset, 'key');
        $this->valueTypes->checkValueType($value, 'value');

        // Convert the key to an index.
        $index = Types::getUniqueString($offset);

        // Store the key-value pair in the items array.
        $this->items[$index] = new Pair($offset, $value);
    }

    /**
     * Unset an item by key.
     *
     * @param mixed $offset The key to unset.
     * @return void
     * @throws InvalidArgumentException If the offset (key) has a disallowed type.
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

    // region IteratorAggregate implementation

    /**
     * Get iterator for foreach loops.
     *
     * @return Traversable<mixed, mixed>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        // This loop ignores the indexes, and returns the keys and values from the pairs.
        foreach ($this->items as $pair) {
            /** @var Pair $pair */
            yield $pair->key => $pair->value;
        }
    }

    // endregion

    // region Helper methods

    /**
     * Validate an key (a.k.a. offset) argument.
     *
     * @param mixed $key The key to validate.
     * @return string The key as a corresponding index string.
     * @throws InvalidArgumentException If the key has a disallowed type.
     * @throws OutOfBoundsException If the key does not exist in the Dictionary.
     */
    private function checkKey(mixed $key): string
    {
        // Check the key type is valid.
        $this->keyTypes->checkValueType($key, 'key');

        // Convert the key to an index.
        $index = Types::getUniqueString($key);

        // Check index (and thus key) exists in the Dictionary.
        if (!array_key_exists($index, $this->items)) {
            throw new OutOfBoundsException('Unknown key: ' . Stringify::abbrev($key) . '.');
        }

        return $index;
    }

    // endregion
}
