<?php

declare(strict_types = 1);

namespace Galaxon\Collections;

// Interfaces
use ArrayAccess;

// Throwables
use ValueError;
use TypeError;
use OutOfRangeException;
use UnderflowException;

// Attributes
use Override;

/**
 * A sequence implementation that is stricter than ordinary PHP arrays.
 *
 * 1. Indexes are always sequential integers starting from 0, as with PHP lists. The largest index (a.k.a. offset or
 * key) will equal the number of items in the sequence minus 1.
 *
 * 2. Allowed types for list items can be specified in the constructor, enabling the Sequence to function like a generic
 * array (or equivalent) in C# or Java, i.e. `new Sequence('int')` is equivalent to `new List<int>` in C#.
 * Allowed types must be specified as strings (with union types and nullable syntax supported), or an iterable with
 * values equal to type names.
 * (NB: Intersection types and DNF syntax is currently NOT supported.)
 *
 * 3. Sequence items can be set at positions beyond the current range, but intermediate items will be filled in with a
 * default value. Sensible defaults are used for common types, or a default value can be specified in the constructor.
 * If the default is an object, all items set to the default will clone the provided object.
 * The default value cannot be a resource.
 *
 * 4. Array-like access using square brackets [] is supported via ArrayAccess and Iterator interfaces.
 *
 * ## Supported Type Specifications
 *
 * ### Basic Types
 * - 'string' - String values
 * - 'int' - Integer values
 * - 'float' - Floating point values
 * - 'bool' - Boolean values
 * - 'null' - Null values
 * - 'array' - Array values
 * - 'object' - Any object instance
 * - 'resource' - Any resource type
 *
 * ### Pseudotypes
 * - 'mixed' - Any type (no restrictions)
 * - 'scalar' - Any scalar type (string|int|float|bool)
 * - 'iterable' - Arrays, iterators, generators (anything iterable)
 * - 'callable' - Functions, methods, closures, invokables
 *
 * ### Custom pseudotypes invented for this package
 * - 'number' - Either number type (int|float)
 * - 'uint' - Unsigned integer type (int where value >= 0)
 *
 * ### Specific Types
 * - Class names: 'DateTime', 'MyNameSpace\MyClass' (leading '\' optional; includes inheritance)
 * - Interface names: 'Countable', 'JsonSerializable' (includes implementations)
 * - Resource types: 'resource (stream)', 'resource (curl)', etc.
 *
 * ### Union Types
 * Use pipe syntax to allow multiple types:
 * - 'string|int' - String OR integer values
 * - 'array|object' - Array OR object values
 * - 'DateTime|null' - DateTime objects OR null
 *
 * ### Nullable Types
 * Use question mark syntax for nullable types:
 * - '?string' - Equivalent to 'string|null'
 * - '?DateTime' - Equivalent to 'DateTime|null'
 *
 * ### Unsupported pseudo-types: void, never, false, true, self, static.
 *
 * ## Automatic Defaults
 * For value types, the following defaults are used:
 * - null or mixed → null
 * - int, uint, number, or scalar → 0
 * - float → 0.0
 * - string → '' (empty string)
 * - bool → false
 * - array → [] (empty array)
 * If the allowed types only include classes, then a default value must be provided, which will be cloned as needed.
 * A resource type cannot be provided as a default value.
 *
 * @example Basic usage
 * $strings = new Sequence('string');
 * $strings->append('hello');
 * $strings[] = 'world';
 *
 * @example Union types with custom default
 * $mixed = new Sequence('string|int', 'default');
 * $mixed->append('text');
 * $mixed->append(42);
 *
 * @example Object types
 * $dates = new Sequence('DateTime', new DateTime());
 * $dates->append(new DateTime('tomorrow'));
 *
 * @example Interface types
 * $countables = new Sequence('Countable', []);
 * $countables->append([1, 2, 3]);         // Arrays are countable
 * $countables->append(new ArrayObject()); // ArrayObject implements Countable
 */
final class Sequence extends Collection implements ArrayAccess
{
    // region Properties

    /**
     * The default value.
     *
     * @var mixed
     */
    private(set) mixed $defaultValue;

    // endregion

    // region Constructor and factory methods

    /**
     * Create a new sequence, with optional type restriction and default value.
     *
     * A default value may be specified, which is used to fill gaps when increasing the sequence length or calling
     * fill(). It cannot be a resource, as these can't be copied.
     * If the default value is not provided, it will be determined automatically for non-objects.
     * If the default value is an object, it will be cloned when needed, so the object provided to the constructor will
     * not actually be used.
     *
     * @param string|iterable|null $types Type specification (e.g., 'string', 'int|null', ['string', 'int']).
     * The default is null, which means values of any type are allowed.
     * @param mixed $default_value Default value for new items (default null). It cannot be a resource.
     * @throws ValueError If no default value is provided and none can be determined.
     * @throws TypeError If a default value is provided, but it's not valid for the specified type set.
     */
    public function __construct(string|iterable|null $types = null, mixed $default_value = null)
    {
        parent::__construct($types);

        // If a default value isn't specified, try to determine a sane default for common types.
        if (func_num_args() === 1) {
            if (!$this->valueTypes->tryGetDefaultValue($default_value)) {
                throw new ValueError("Default value must be provided (or allow nulls).");
            }
        } elseif (!$this->valueTypes->match($default_value) || is_resource($default_value)) {
            // The default value is invalid for the specified type set.
            throw new TypeError("Default value has an invalid type.");
        }

        // Set the default value.
        $this->defaultValue = $default_value;
    }

    /**
     * Construct a new sequence by copying values and their types from a source iterable.
     *
     * @param iterable $src The iterable to copy from.
     * @return static The new sequence instance.
     */
    public static function fromIterable(iterable $src): static {
        $seq = new self();

        // Add types from the source iterable.
        foreach ($src as $item) {
            // Add the item type to the sequence types, if requested.
            $seq->valueTypes->addValueType($item);

            // Add the item to the sequence.
            $seq->append($item);
        }

        return $seq;
    }

    /**
     * Create a new sequence with the same types and default value as the calling object, and items copied from a
     * source iterable (typically items from the calling Sequence, although not necessarily).
     *
     * @param iterable $items The iterable to copy items from.
     * @return self The new sequence.
     */
    public function fromSubset(iterable $items = []): self {
        // Construct the new sequence.
        $seq = new self($this->valueTypes, $this->defaultValue);

        // Copy items.
        $seq->append(...$items);

        return $seq;
    }

    // endregion

    // region Checking methods

    /**
     * Validate index parameters and optionally check bounds.
     *
     * @param mixed $index The index to validate.
     * @param bool $check_lower_bound Whether to check if an index is non-negative.
     * @param bool $check_upper_bound Whether to check if an index is within array bounds.
     * @throws TypeError If the index is not an integer.
     * @throws OutOfRangeException If the index is outside the valid range for the sequence.
     */
    private function checkIndex(mixed $index, bool $check_lower_bound = true, bool $check_upper_bound = true): void
    {
        // Check the index is an integer.
        if (!is_int($index)) {
            throw Type::createError('index', 'int', $index);
        }

        // Check the index isn't negative.
        if ($check_lower_bound && $index < 0) {
            throw new OutOfRangeException("Index cannot be negative.");
        }

        // Check the index isn't too large.
        if ($check_upper_bound && $index >= count($this->items)) {
            throw new OutOfRangeException("Index is out of range.");
        }
    }

    /**
     * Gets a new default value for the sequence.
     *
     * If the provided default value is an object, a clone of it is returned.
     * Otherwise the default value will have value semantics, so no clone operation is required.
     *
     * @return mixed The new default value.
     */
    private function getDefaultValue(): mixed {
        return is_object($this->defaultValue) ? clone $this->defaultValue : $this->defaultValue;
    }

    // endregion

    // region Add items to the sequence

    /**
     * Add one or more items to the end of the sequence.
     *
     * NB: This is a mutating method.
     *
     * @param mixed ...$items The items to add to the sequence.
     * @return $this The sequence instance.
     *
     * @example
     * $sequence->append($item);
     * $sequence->append($item1, $item2, $item3);
     * $sequence->append(...$items);
     */
    public function append(mixed ...$items): self
    {
        foreach ($items as $item) {
            // Check the item type.
            $this->valueTypes->checkType($item);

            // Append an element to the end of the sequence.
            $this->items[] = $item;
        }

        // Return this for chaining.
        return $this;
    }

    /**
     * Add one or more items to the start of the sequence.
     *
     * NB: This is a mutating method.
     *
     * @param mixed ...$items The items to add to the sequence.
     * @return $this The sequence instance.
     *
     * @example
     * $sequence->prepend($item);
     * $sequence->prepend($item1, $item2, $item3);
     * $sequence->prepend(...$items);
     */
    public function prepend(mixed ...$items): self
    {
        foreach ($items as $item) {
            // Check the item type.
            $this->valueTypes->checkType($item);

            // Prepand an element at the start of the sequence.
            array_unshift($this->items, $item);
        }

        // Return this for chaining.
        return $this;
    }

    /**
     * Insert an item at the specified position. Later items will be shifted right.
     *
     * NB: This is a mutating method.
     *
     * @param int $index The zero-based index position to insert the item at.
     * @param mixed $item The item to insert.
     * @return $this The sequence instance.
     */
    public function insert(int $index, mixed $item): self {
        // Check the item type.
        $this->valueTypes->checkType($item);

        // For indexes beyond the end of the sequence, no items need to be shifted, so defer to offsetSet().
        $orig_count = count($this->items);
        if ($index >= $orig_count) {
            $this->offsetSet($index, $item);
            return $this;
        }

        // Ensure the index is valid.
        $this->checkIndex($index);

        // Shift elements after $index right by 1.
        for ($j = $orig_count; $j > $index; $j--) {
            $this->items[$j] = $this->items[$j - 1];
        }

        // Set the new value of the item at position $index.
        $this->items[$index] = $item;

        // Return this for chaining.
        return $this;
    }

    // endregion

    // region Remove items from the sequence

    /**
     * Remove the item at the given index from the sequence.
     *
     * The indexes of items at higher indexes than the one specified by $index will be reduced by 1, i.e. shifted down,
     * and the sequence length will be reduced by 1.
     *
     * NB: This is a mutating method.
     *
     * @param int $index The zero-based index position of the item to remove.
     * @return mixed The removed value.
     * @throws TypeError If the index is not an integer.
     * @throws OutOfRangeException If the index is outside the valid range for the sequence.
     */
    public function remove(int $index): mixed
    {
        // Ensure the index is valid.
        $this->checkIndex($index);

        // Get the item.
        $item = $this->items[$index];

        // Remove it from the sequence.
        array_splice($this->items, $index, 1);

        // Return the item.
        return $item;
    }

    /**
     * Remove all items matching a given value. Strict equality is used to find matching values.
     *
     * NB: This is a mutating method.
     *
     * @param mixed $value The value to remove.
     * @return int The number of items removed.
     */
    public function removeByValue(mixed $value): int
    {
        // Get the number of items in the sequence.
        $orig_count = count($this->items);

        // Filter the sequence to remove the matching values.
        $this->items = array_values(array_filter(
            $this->items,
            static fn($item) => $item !== $value
        ));

        // Return the number of items removed.
        return $orig_count - count($this->items);
    }

    /**
     * Remove the first item from the sequence.
     *
     * NB: This is a mutating method.
     *
     * @return mixed The removed item.
     * @throws UnderflowException If the sequence is empty.
     */
    public function removeFirst(): mixed
    {
        // Check for an empty sequence.
        if (count($this->items) === 0) {
            throw new UnderflowException("No items in the sequence.");
        }

        // Remove and return the first item.
        return array_shift($this->items);
    }

    /**
     * Remove the last item from the sequence.
     *
     * NB: This is a mutating method.
     *
     * @return mixed The removed item.
     * @throws UnderflowException If the sequence is empty.
     */
    public function removeLast(): mixed
    {
        // Check for an empty sequence.
        if (count($this->items) === 0) {
            throw new UnderflowException("No items in the sequence.");
        }

        // Remove and return the last item.
        return array_pop($this->items);
    }

    // endregion

    // region Get items from the sequence

    /**
     * Get the first item from the sequence.
     *
     * @return mixed The first item.
     * @throws OutOfRangeException If the sequence is empty.
     */
    public function first(): mixed
    {
        return $this[0];
    }

    /**
     * Get the last item from the sequence.
     *
     * @return mixed The last item.
     * @throws OutOfRangeException If the sequence is empty.
     */
    public function last(): mixed
    {
        return $this[array_key_last($this->items)];
    }

    /**
     * Get a slice of the sequence.
     *
     * Both the index and the length can be negative. They work the same as for array_slice().
     * @see https://www.php.net/manual/en/function.array-slice.php
     *
     * @param int $index The start position of the slice.
     *      If non-negative, the slice will start at that index in the sequence.
     *      If negative, the slice will start that far from the end of the sequence.
     * @param ?int $length The length of the slice.
     *      If given and is positive, then the sequence will have up to that many elements in it.
     *      If the sequence is shorter than the length, then only available items will be present.
     *      If given and is negative, the slice will stop that many elements from the end of the sequence.
     *      If omitted or null, then the slice will include everything from index up until the end of the sequence.
     * @return self The slice.
     */
    public function slice(int $index, ?int $length = null): self
    {
        // Get the items.
        $items = array_slice($this->items, $index, $length);

        // Construct the result.
        return $this->fromSubset($items);
    }

    /**
     * Searches the array for a given value and returns the first corresponding key if successful.
     *
     * This method is analogous to array_search() except it returns null if the value is not found.
     * @see https://www.php.net/manual/en/function.array-search.php
     *
     * @param mixed $value The value to search for.
     * @return int|null The index of the first matching value, or null if the value is not found.
     */
    public function search(mixed $value): ?int
    {
        $result = array_search($value, $this->items, true);
        return $result !== false ? $result : null;
    }

    /**
     * Returns the first element satisfying a callback function.
     *
     * This method is analogous to array_find().
     * @see https://www.php.net/manual/en/function.array-find.php
     *
     * @param callable $fn The filter function that will return true for a matching item.
     * @return mixed The value of the first element for which the callback returns true. If no matching element is found
     *      the function returns null.
     */
    public function find(callable $fn): mixed
    {
        return array_find($this->items, $fn);
    }

    // endregion

    // region Inspection methods

    /**
     * Check if the Sequence contains one or more values.
     *
     * Strict equality is used to compare values, i.e. the item must match on both value and type.
     *
     * @param mixed ...$items The items to check for.
     * @return bool True if the Sequence contains all the items, false otherwise.
     */
    public function contains(mixed ...$items): bool
    {
        // Check each item.
        foreach ($items as $item) {
            if (!in_array($item, $this->items, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if all items in the sequence pass a test.
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
     * Check if any items in the sequence pass a test.
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

    // region Sort methods

    /**
     * Return a new sequence with the items sorted in ascending order.
     *
     * This method is analogous to sort(), except that it's non-mutating.
     * @see https://www.php.net/manual/en/function.sort.php
     *
     * @param int $flags The sorting flags.
     * @return self The sorted sequence.
     */
    public function sort(int $flags = SORT_REGULAR): self
    {
        // Copy the items array so the method is non-mutating.
        $items = $this->items;
        sort($items, $flags);

        // Construct the result.
        return $this->fromSubset($items);
    }

    /**
     * Return a new sequence with the items sorted in descending order.
     *
     * This method is analogous to rsort(), except that it's non-mutating.
     * @see https://www.php.net/manual/en/function.rsort.php
     *
     * @param int $flags The sorting flags.
     * @return self The sorted sequence.
     */
    public function sortReverse(int $flags = SORT_REGULAR): self
    {
        // Copy the items array so the method is non-mutating.
        $items = $this->items;
        rsort($items, $flags);

        // Construct the result.
        return $this->fromSubset($items);
    }

    /**
     * Return a new sequence with the items sorted using a custom comparison function.
     *
     * This method is analogous to usort(), except that it's non-mutating.
     * @see https://www.php.net/manual/en/function.usort.php
     *
     * @param callable $fn The comparison function.
     * @return self The sorted sequence.
     */
    public function sortBy(callable $fn): self
    {
        // Copy the items array so the method is non-mutating.
        $items = $this->items;
        usort($items, $fn);

        // Construct the result.
        return $this->fromSubset($items);
    }

    // endregion

    // region Miscellaneous methods

    /**
     * Split the sequence into chunks of a given size.
     * The last chunk may be smaller than the specified size.
     *
     * This method is analogous to array_chunk().
     * @see https://www.php.net/manual/en/function.array-chunk.php
     *
     * @param int $size The size of each chunk.
     * @return self[] An array of sequences representing the chunks.
     */
    public function chunk(int $size): array
    {
        // Break the array of items into chunks.
        $chunks = array_chunk($this->items, $size);

        // Convert the chunks into sequences.
        $result = [];
        foreach ($chunks as $chunk) {
            $result[] = $this->fromSubset($chunk);
        }

        return $result;
    }

    /**
     * Counts the occurrences of each distinct value in a sequence.
     *
     * This method is analogous to array_count_values().
     * @see https://www.php.net/manual/en/function.array-count-values.php
     *
     * @return Dictionary A dictionary mapping values to the number of occurrences.
     */
    public function countValues(): Dictionary
    {
        // Construct the dictionary.
        $value_count = new Dictionary($this->valueTypes, 'int');

        // Count the occurrences of each distinct value.
        foreach ($this->items as $item) {
            if ($value_count->offsetExists($item)) {
                $value_count[$item]++;
            }
            else {
                $value_count[$item] = 1;
            }
        }

        return $value_count;
    }

    /**
     * Fill the sequence with a given value.
     *
     * This method is analogous to array_fill().
     * @see https://www.php.net/manual/en/function.array-fill.php
     *
     * @param int $start_index The zero-based index position to start filling.
     * @param int $count The number of items to fill.
     * @param mixed $value The value to fill with.
     * @return $this The calling object, for chaining.
     */
    public function fill(int $start_index, int $count, mixed $value = null): self
    {
        // If no value is specified, use the default value.
        // Use func_num_args() here to check if the value was specified, instead of comparing it with null, because they
        // might actually want to fill with nulls.
        $use_default_value = func_num_args() === 2;

        // Set the specified sequence items.
        for ($i = 0; $i < $count; $i++) {
            $this[$start_index + $i] = $use_default_value ? $this->getDefaultValue() : $value;
        }

        return $this;
    }

    /**
     * Return a sequence with all items matching a certain filter.
     *
     * This method is analogous to array_filter().
     * @see https://www.php.net/manual/en/function.array-filter.php
     *
     * @param callable $fn The filter function that returns true for items to keep.
     * @return self A new sequence containing only the matching items.
     */
    public function filter(callable $fn): self
    {
        // Get the matching values.
        $items = array_filter($this->items, static fn($item) => $fn($item));

        // Construct the result.
        return $this->fromSubset($items);
    }

    /**
     * Applies the callback to the items in the sequence.
     *
     * @param callable $fn The callback function to apply to each item.
     * @return self A new sequence containing the results of the callback function.
     */
    public function map(callable $fn): self
    {
        // Apply the callback to each item.
        $items = array_map($fn, $this->items);

        // Construct the result.
        // Use fromIterable() rather than fromSubset() because we don't know the types returned from the callback.
        return self::fromIterable($items);
    }

    /**
     * Merge two sequences.
     *
     * @param Sequence $other The sequence to merge with.
     * @return self A new sequence containing the merged items.
     */
    public function merge(self $other): self
    {
        // Merge the two sequences.
        $items = array_merge($this->items, $other->items);

        // Construct the result.
        return $this->fromSubset($items);
    }

    /**
     * Return a new sequence with the same items as the $this sequence but in reverse order.
     *
     * @return self A new sequence with the same items as the $this sequence but in reverse order.
     */
    public function reverse(): self
    {
        // Reverse the items.
        $items = array_reverse($this->items);

        // Construct the result.
        return $this->fromSubset($items);
    }

    /**
     * Get the unique values from the sequence.
     *
     * This method is analogous to array_unique().
     * @see https://www.php.net/manual/en/function.array-unique.php
     *
     * @return self A new sequence containing the unique values.
     */
    public function unique(): self {
        // Get the unique values.
        $items = array_unique($this->items);

        // Construct the result.
        return $this->fromSubset($items);
    }

    // endregion

    // region Aggregation methods

    /**
     * Reduce the sequence to a single value using a callback function.
     *
     * @param callable $fn Callback function (accumulator, item) => new_accumulator.
     * @param mixed $init Initial value for the aggregation.
     * @return mixed The final result.
     */
    public function reduce(callable $fn, mixed $init): mixed
    {
        return array_reduce($this->items, $fn, $init);
    }

    /**
     * Find the product of the values in the sequence.
     *
     * @return float|int The product of the values in the sequence.
     */
    public function product(): float|int
    {
        return array_product($this->items);
    }

    /**
     * Find the sum of the values in the sequence.
     *
     * @return float|int The sum of the values in the sequence.
     */
    public function sum(): float|int
    {
        return array_sum($this->items);
    }

    /**
     * Find the concatenation of the values in the sequence, optionally separated by a given string (the "glue").
     *
     * NB: This method is analogous to implode().
     * @see https://www.php.net/manual/en/function.implode.php
     *
     * It may generate an error or throw an exception if the sequence contains an object that doesn't implement
     * __toString(). TODO - test.
     *
     *
     * @param string $glue The string to separate the values with.
     * @return string The concatenation of the values in the sequence.
     */
    public function join(string $glue = ''): string
    {
        return implode($glue, $this->items);
    }

    // endregion

    // region Random methods

    /**
     * Randomly choose one or more items from the sequence.
     * Both keys and values are returned, as an array.
     *
     * NB: This method is non-mutating.
     *
     * @param int $count The number of items to choose (default: 1).
     * @return array An array containing the chosen items (keys and values) in random order.
     * @throws UnderflowException If the sequence is empty.
     * @throws OutOfRangeException If the count is out of range.
     * @throws OutOfRangeException If the sequence doesn't have enough items to choose the specified count.'
     */
    public function chooseRand(int $count = 1): array
    {
        // Guards.
        if ($this->count() === 0) {
            throw new UnderflowException("No items in the sequence to choose from.");
        }
        if ($count <= 0) {
            throw new OutOfRangeException("Count must be greater than 0.");
        }
        if ($count > $this->count()) {
            throw new OutOfRangeException("Not enough items in the sequence to choose $count items.");
        }

        // Get the keys of the randomly chosen items.
        $keys = array_rand($this->items, $count);

        // Make sure it's an array, as array_rand() will return a single key value when count is 1.
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        // Convert the keys into key-value pairs.
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->items[$key];
        }
        return $result;
    }

    /**
     * Randomly remove one or more items from the sequence.
     *
     * NB: This method is mutating.
     *
     * @param int $count The number of items to remove (default: 1).
     * @return array An array containing the removed values in random order.
     * @throws UnderflowException If the sequence is empty.
     * @throws OutOfRangeException If the count is out of range.
     * @throws OutOfRangeException If the sequence doesn't have enough items to choose the specified count.'
     */
    public function removeRand(int $count = 1): array
    {
        // Randomly choose one or more items.
        $items = $this->chooseRand($count);

        // Sort the items by key in descending order.
        // We want to remove the items with the highest keys first,
        // because each call to remove() will re-index the sequence.
        // Clone the array of selected items to preserve randomness.
        $sorted_items = $items;
        krsort($sorted_items);

        // Remove the items from the sequence.
        foreach ($sorted_items as $key => $value) {
            $this->remove($key);
        }

        // Return the chosen values.
        return array_values($items);
    }

    // endregion

    // region ArrayAccess implementation

    /**
     * Append or set a sequence item.
     *
     * If the index is out of range, the sequence will be increased in size to accommodate it.
     * Any intermediate positions will be filled with the default value.
     * NB: If the default is an object, all items set to the default will clone the object.
     * If you don't want this behaviour, don't rely on it; set each sequence item individually.
     *
     * @param mixed $offset The zero-based index position to set, or null to append.
     * @param mixed $value The value to set.
     * @throws TypeError If the index is neither null nor an integer.
     * @throws OutOfRangeException If the index is out of range.
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Check the item has a valid type.
        $this->valueTypes->checkType($value);

        if ($offset === null) {
            // Append a new item to the sequence.
            // $sequence[] = $value
            $this->append($value);
        }
        else {
            // Update an item.
            // $sequence[$key] = $value

            // Check the index is valid.
            $this->checkIndex($offset, check_upper_bound: false);

            // Fill in any missing items with defaults.
            $start = count($this->items);
            for ($i = $start; $i < $offset; $i++) {
                $this->items[$i] = $this->getDefaultValue();
            }

            // Set the item value.
            $this->items[$offset] = $value;
        }
    }

    /**
     * Get a value from the sequence.
     *
     * @param mixed $offset The zero-based index position to get.
     * @return mixed The value at the specified index.
     * @throws TypeError If the index is not an integer.
     * @throws OutOfRangeException If the index is outside the valid range for the sequence.
     */
    #[Override]
    public function offsetGet(mixed $offset): mixed
    {
        // Check the index is valid.
        $this->checkIndex($offset);

        // Get the item at the specified index.
        return $this->items[$offset];
    }

    /**
     * Check if a given index is valid.
     *
     * @param mixed $offset The sequence index position.
     * @return bool If the given index is an integer and within the current valid range for the sequence.
     * @throws TypeError If the index is not an integer.
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        // Check the index is an integer.
        if (!is_int($offset)) {
            throw Type::createError('offset', 'int', $offset);
        }

        return array_key_exists($offset, $this->items);
    }

    /**
     * Set a sequence item to null.
     *
     * This method isn't usually called as a method, but rather indirectly by calling unset($sequence[$offset]).
     *
     * Doing this doesn't remove an item from the sequence, as it does with ordinary PHP arrays. This is because this
     * data structure maintains zero-indexed sequential keys. Therefore, removing an item from the sequence would
     * require re-indexing later items. This could be unexpected behavior.
     *
     * To remove an item from the sequence, use one of the remove*() methods.
     *
     * @param mixed $offset The zero-based index position to unset.
     * @throws OutOfRangeException If the index is outside the valid range for the sequence.
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        // Check the index is valid.
        $this->checkIndex($offset);

        // Make sure nulls are allowed.
        if (!$this->valueTypes->nullOk()) {
            throw new TypeError("Cannot unset an item if null is not an allowed type.");
        }

        // Set the item to null.
        $this->items[$offset] = null;
    }

    // endregion

    // region Conversion methods

    /**
     * Convert the Sequence to a Dictionary. The Sequence's indexes will be keys in the new Dictionary.
     *
     * @return Dictionary The new Dictionary.
     */
    public function toDictionary(): Dictionary {
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
     * Convert the Sequence to a Set. The resulting Set will contain only unique values from the Sequence.
     *
     * @return Set The new Set.
     */
    public function toSet(): Set {
        // Create the new Set.
        $set = new Set($this->valueTypes);

        // Get the unique values.
        $items = array_unique($this->items);

        // Add the unique values to the Set.
        foreach ($items as $item) {
            $set->add($item);
        }

        // Return the new Set.
        return $set;
    }

    // endregion
}
