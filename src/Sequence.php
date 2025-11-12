<?php

declare(strict_types = 1);

namespace Galaxon\Collections;

use ArithmeticError;
use ArrayAccess;
use ArrayIterator;
use Galaxon\Core\Types;
use OutOfRangeException;
use Override;
use Traversable;
use TypeError;
use UnderflowException;
use ValueError;

/**
 * A type-specific list implementation.
 *
 * 1. Indexes are always sequential integers starting from 0, as with PHP lists. The largest index (a.k.a. offset or
 * key) will equal the number of items in the Sequence minus 1.
 *
 * 2. Allowed types for Sequence values can be specified in the constructor, enabling the Sequence to function like a
 * generic array (or equivalent) in C# or Java, i.e. `new Sequence('int')` is equivalent to `new List<int>` in C#.
 * Allowed types can be specified as strings (with union types and nullable syntax supported), or an iterable of string
 * values equal to type names. These are two different ways to specify multiple types and can't be combined; so you
 * can't specify, for example, `['int|string', '?float]`. Specifying null for the allowed types (not 'null') means
 * values of any type are allowed. See TypeSet for more details.
 * (NB: Intersection types and DNF syntax are currently NOT supported.)
 *
 * 3. Sequence items can be set at positions beyond the current range, but intermediate items will be filled in with a
 * default value. Sensible defaults are used for common types, or a default value can be specified in the constructor.
 * If the default value is an object, it will be cloned as needed.
 *
 * 4. Array-like access using square brackets [] and iteration using foreach is supported via the ArrayAccess and
 * Iterator interfaces.
 *
 * ## Automatic Defaults
 * For value types, the following defaults are used:
 * - null or mixed → null
 * - int, uint, number, or scalar → 0
 * - float → 0.0
 * - string → '' (empty string)
 * - bool → false
 * - array or iterable → [] (empty array)
 * For other types (i.e. resource, object, interface, callable), the automatic default value will be null, and if 'null'
 * has not been specified in the allowed types, it will be automatically added.
 *
 * ## Implementation Notes
 * Several methods use `func_num_args()` to distinguish between "parameter omitted" and "parameter explicitly set to
 * null". This is necessary because PHP doesn't provide a native way to make this distinction with optional parameters.
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
     * Create a new Sequence, with optional type restriction, default value, and value source.
     *
     * The allowed types for values in the Sequence can be specified in several ways:
     * - null = Values of any type are allowed.
     * - string = A type name, or multiple types using union type or nullable type syntax, e.g. 'string', 'int|null', '?int'
     * - iterable = Array or other collection of type names, e.g. ['string', 'int']
     * - true = The types will be inferred from the source iterable's values.
     *
     * A default value may be specified, which is used to fill gaps when increasing the Sequence length as a side
     * effect of calling insert() or offsetSet() (either directly or via square bracket syntax).
     * If a default value is not provided (or is null), it will be determined automatically.
     * If the default value is an object, it will be cloned as needed.
     * It doesn't really make sense to have a resource as a default value, but it's allowed.
     *
     * If a source iterable is provided, the Sequence will be initialized with values from the iterable.
     *
     * @param null|string|iterable|true $types Allowed types for values in the Sequence (default true, for infer).
     * @param mixed $default_value Default value for new items (default null).
     * @param iterable $source A source iterable to import values from (optional).
     * @throws ValueError If a type name is invalid.
     * @throws TypeError If a type is not specified as a string, or any imported values have disallowed types.
     */
    public function __construct(
        null|string|iterable|true $types = true,
        mixed $default_value = null,
        iterable $source = []
    )
    {
        // Determine if we should infer types from the source iterable.
        $infer = $types === true;

        // Instantiate the object and typeset.
        parent::__construct($infer ? null : $types);

        // Import values from the source iterable.
        foreach ($source as $item) {
            // Collect types from the source iterable if requested.
            if ($infer) {
                $this->valueTypes->addValueType($item);
            }

            // Add item to the new Sequence.
            $this->append($item);
        }

        // Set the default value.
        // We do this after the value types are known, in case the default value wasn't supplied and needs to be
        // inferred from the value typeset.
        // Check if we should infer the default value from the allowed types.
        if ($default_value === null) {
            // Try to determine a sane default for common types.
            if (!$this->valueTypes->tryInferDefaultValue($default_value)) {
                // If no default value could be inferred, use null and allow nulls in the Sequence.
                $this->valueTypes->add('null');
            }
        }
        elseif (!$this->valueTypes->match($default_value)) {
            // The default value has a disallowed type.
            throw new TypeError(
                'The default value has an invalid type. ' .
                'Expected one of: ' . $this->valueTypes . ', ' .
                'but got: ' . Types::getBasicType($default_value) . '.'
            );
        }

        // Set the default value.
        $this->defaultValue = $default_value;
    }

    /**
     * Generate a new Sequence of numbers spanning a given range, using a given step size.
     *
     * A step size of 0 is not allowed, as this would cause an infinite loop.
     *
     * This method is analogous to range(), except it does not support strings.
     * @see https://www.php.net/manual/en/function.range.php
     *
     * Note: When using floats, be aware of floating-point precision issues.
     * The final value may not be exactly $end due to rounding errors.
     *
     * @example
     * Sequence::range(1, 10);          // [1, 2, 3, ..., 10]
     * Sequence::range(0, 1, 0.1);      // [0.0, 0.1, 0.2, ..., 1.0]
     * Sequence::range(10, 1, -1);      // [10, 9, 8, ..., 1]
     *
     * @param int|float $start The start of the range.
     * @param int|float $end The end of the range.
     * @param int|float $step The step size (default 1).
     * @return self The new Sequence.
     * @throws ValueError If the step size is invalid for the range specified.
     */
    public static function range(int|float $start, int|float $end, int|float $step = 1): self
    {
        // Validate step size. Use loose comparison here to validate either an int or float argument.
        if ($step == 0) {
            throw new ValueError("The step size cannot be zero.");
        }
        if ($start <= $end && $step < 0) {
            throw new ValueError("The step size must be positive for an increasing range.");
        }
        if ($start >= $end && $step > 0) {
            throw new ValueError("The step size must be negative for a decreasing range.");
        }

        // If any of the arguments are floats, generate a Sequence of floats; otherwise, ints.
        $type = is_float($start) || is_float($end) || is_float($step) ? 'float' : 'int';

        // Construct the new Sequence.
        $seq = new self($type);

        // Cast $start to float, if necessary, to ensure all generated values are also float.
        if ($type === 'float') {
            $start = (float)$start;
        }

        // Generate the range.
        if ($step > 0) {
            // Ascending.
            for ($i = $start; $i <= $end; $i += $step) {
                $seq->append($i);
            }
        }
        else {
            // Descending.
            for ($i = $start; $i >= $end; $i += $step) {
                $seq->append($i);
            }
        }

        // Return the new Sequence.
        return $seq;
    }

    // endregion

    // region Private helper methods

    /**
     * Validate an index (a.k.a. offset) argument.
     *
     * @param mixed $index The index to validate.
     * @param bool $check_upper_bound Whether to check if an index is within array bounds.
     * @throws TypeError If the index is not an integer.
     * @throws OutOfRangeException If the index is outside the valid range for the Sequence.
     */
    private function checkIndex(mixed $index, bool $check_upper_bound = true): void
    {
        // Check the index is an integer.
        if (!is_int($index)) {
            throw Types::createError('index', 'int', $index);
        }

        // Check the index isn't negative.
        if ($index < 0) {
            throw new OutOfRangeException("Index cannot be negative.");
        }

        // Check the index isn't too large.
        if ($check_upper_bound && $index >= count($this->items)) {
            throw new OutOfRangeException("Index is out of range.");
        }
    }

    /**
     * Get a new default value.
     *
     * If the default value is an object, clone it.
     * This behavior will probably be more useful than filling a Sequence with references to the same object.
     *
     * @return mixed The new default value.
     */
    private function getDefaultValue(): mixed
    {
        return is_object($this->defaultValue) ? clone $this->defaultValue : $this->defaultValue;
    }

    /**
     * Create a new Sequence with the same types and default value as the calling object, and items copied from a
     * source iterable (typically items from the calling Sequence, although this is not enforced).
     *
     * @param iterable $items The iterable to copy items from.
     * @return self The new Sequence.
     */
    private function fromSubset(iterable $items): self
    {
        // Construct the new Sequence.
        return new self($this->valueTypes, $this->defaultValue, $items);
    }

    // endregion

    // region Add items to the Sequence

    /**
     * Add one or more items to the end of the Sequence.
     *
     * NB: This is a mutating method.
     *
     * @param mixed ...$items The items to add to the Sequence.
     * @return $this The Sequence instance.
     * @throws TypeError If any of the provided items have an invalid type.
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
            $this->valueTypes->check($item);

            // Append an element to the end of the Sequence.
            $this->items[] = $item;
        }

        // Return this for chaining.
        return $this;
    }

    /**
     * Add one or more items to the start of the Sequence.
     *
     * NB: This is a mutating method.
     *
     * Note that the list of elements is prepended as a whole, so that the prepended elements stay in the same order.
     * This behaviour parallels array_unshift().
     * @see https://www.php.net/manual/en/function.array-unshift.php
     *
     * @param mixed ...$items The items to add to the Sequence.
     * @return $this The Sequence instance.
     *
     * @example
     * $sequence->prepend($item);
     * $sequence->prepend($item1, $item2, $item3);
     * $sequence->prepend(...$items);
     */
    public function prepend(mixed ...$items): self
    {
        // Go through the items in reverse order.
        for ($i = count($items) - 1; $i >= 0; $i--) {
            $item = $items[$i];

            // Check the item type.
            $this->valueTypes->check($item);

            // Prepend an element at the start of the Sequence.
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
     * @return $this The Sequence instance.
     * @throws OutOfRangeException If the index is less than 0.
     */
    public function insert(int $index, mixed $item): self
    {
        // Check the item type.
        $this->valueTypes->check($item);

        // Check the index is valid.
        $this->checkIndex($index, false);

        // For indexes beyond the end of the Sequence, no items need to be shifted, so defer to offsetSet().
        // That will take care of gap-filling with default values.
        $orig_count = count($this->items);
        if ($index >= $orig_count) {
            $this->offsetSet($index, $item);
            return $this;
        }

        // Shift elements after $index right by 1.
        for ($j = $orig_count; $j > $index; $j--) {
            $this->items[$j] = $this->items[$j - 1];
        }

        // Set the new value of the item at $index.
        $this->items[$index] = $item;

        // Return this for chaining.
        return $this;
    }

    /**
     * Import values from an iterable into the Sequence.
     *
     * @param iterable $src The source iterable.
     * @return $this The calling object.
     * @throws TypeError If any of the values have a disallowed type.
     */
    #[Override]
    public function import(iterable $src): static
    {
        // Copy items from the source iterable into the Sequence.
        $this->append(...$src);

        // Return this for chaining.
        return $this;
    }

    // endregion

    // region Remove items from the Sequence

    /**
     * Remove the item at the given index from the Sequence.
     *
     * The indexes of items at higher indexes than the one specified by $index will be reduced by 1, i.e. shifted down,
     * and the Sequence length will be reduced by 1.
     *
     * NB: This is a mutating method.
     *
     * @param int $index The zero-based index of the item to remove.
     * @return mixed The removed item.
     * @throws OutOfRangeException If the index is outside the valid range for the Sequence.
     */
    public function removeByIndex(int $index): mixed
    {
        // Check the index is valid.
        $this->checkIndex($index);

        // Remove the item from the Sequence.
        $removed_items = array_splice($this->items, $index, 1);

        // Return the removed item.
        return $removed_items[0];
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
        // Get the initial count of items.
        $init_count = count($this->items);

        // Filter the Sequence to remove the matching values.
        $this->items = array_values(array_filter(
            $this->items,
            static fn($item) => $item !== $value
        ));

        // Return the number of items removed.
        return $init_count - $this->count();
    }

    /**
     * Remove the first item from the Sequence.
     *
     * NB: This is a mutating method.
     *
     * @return mixed The removed item.
     * @throws UnderflowException If the Sequence is empty.
     */
    public function removeFirst(): mixed
    {
        // Check for an empty Sequence.
        if (count($this->items) === 0) {
            throw new UnderflowException("No items in the Sequence.");
        }

        // Remove and return the first item.
        return array_shift($this->items);
    }

    /**
     * Remove the last item from the Sequence.
     *
     * NB: This is a mutating method.
     *
     * @return mixed The removed item.
     * @throws UnderflowException If the Sequence is empty.
     */
    public function removeLast(): mixed
    {
        // Check for an empty Sequence.
        if (count($this->items) === 0) {
            throw new UnderflowException("No items in the Sequence.");
        }

        // Remove and return the last item.
        return array_pop($this->items);
    }

    // endregion

    // region Comparison and inspection methods
    // These are non-mutating and return bools.

    /**
     * Check if the Sequence contains a value.
     *
     * Strict equality is used, i.e. the item must match on both value and type.
     *
     * @param mixed $value The item to check for.
     * @return bool True if the Sequence contains the item, false otherwise.
     */
    #[Override]
    public function contains(mixed $value): bool
    {
        return in_array($value, $this->items, true);
    }

    /**
     * Check if the Sequence is equal to another Collection.
     *
     * "Equal" in this case means that the Sequences have the same:
     * - type (i.e. they are both instances of Sequence)
     * - number of items
     * - item values (strict equality)
     * - order of items
     *
     * Type constraints are not considered, because these are only relevant when adding values to a Sequence.
     * Therefore, if the first Sequence only permits 'int' values whereas the second permits both 'int' and 'string',
     * the two will still compare as equal if the other conditions are met.
     *
     * @param Collection $other The other Sequence.
     * @return bool True if the Sequences are equal, false otherwise.
     */
    #[Override]
    public function equals(Collection $other): bool
    {
        // Check type and item count are equal.
        if (!$this->equalTypeAndCount($other)) {
            return false;
        }

        // Check values are equal.
        return array_all($this->items, fn($value, $key) => $this->items[$key] === $other->items[$key]);
    }

    /**
     * Check if the Sequence contains an index.
     *
     * This is an alias for offsetExists(), which isn't normally called directly.
     * One small difference is that the parameter must be an int.
     * This method has a better name, as the documentation uses "index" rather than "offset" or "key".
     *
     * @param int $index The index to check for.
     * @return bool True if the Sequence contains the index, false otherwise.
     */
    public function indexExists(int $index): bool
    {
        return $this->offsetExists($index);
    }

    // endregion

    // region Get items from the Sequence

    /**
     * Get the first item from the Sequence.
     *
     * @return mixed The first item.
     * @throws OutOfRangeException If the Sequence is empty.
     */
    public function first(): mixed
    {
        // Guard against empty Sequences.
        if ($this->empty()) {
            throw new OutOfRangeException("No items in the Sequence.");
        }

        // Get the first item.
        return $this[0];
    }

    /**
     * Get the last item from the Sequence.
     *
     * @return mixed The last item.
     * @throws OutOfRangeException If the Sequence is empty.
     */
    public function last(): mixed
    {
        // Guard against empty Sequences.
        if ($this->empty()) {
            throw new OutOfRangeException("No items in the Sequence.");
        }

        // Get the last item.
        return $this[array_key_last($this->items)];
    }

    /**
     * Get a slice of the Sequence.
     *
     * Both the index and the length can be negative. They work the same as for array_slice().
     * @see https://www.php.net/manual/en/function.array-slice.php
     *
     * @param int $index The start position of the slice.
     *      If non-negative, the slice will start at that index in the Sequence.
     *      If negative, the slice will start that far from the end of the Sequence.
     * @param ?int $length The length of the slice.
     *      If given and is positive, then the Sequence will have up to that many elements in it.
     *      If the Sequence is shorter than the length, then only available items will be present.
     *      If given and is negative, the slice will stop that many elements from the end of the Sequence.
     *      If omitted or null, then the slice will include everything from index up until the end of the Sequence.
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

    // region Sort methods

    /**
     * Return a new Sequence with the items sorted in ascending order.
     *
     * This method is analogous to sort(), except it's non-mutating.
     * @see https://www.php.net/manual/en/function.sort.php
     *
     * @param int $flags The sorting flags.
     * @return self The sorted Sequence.
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
     * Return a new Sequence with the items sorted in descending order.
     *
     * This method is analogous to rsort(), except it's non-mutating.
     * @see https://www.php.net/manual/en/function.rsort.php
     *
     * @param int $flags The sorting flags.
     * @return self The sorted Sequence.
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
     * Return a new Sequence with the items sorted using a custom comparison function.
     *
     * This method is analogous to usort(), except it's non-mutating.
     * @see https://www.php.net/manual/en/function.usort.php
     *
     * @param callable $fn The comparison function.
     * @return self The sorted Sequence.
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
     * Split the Sequence into chunks of a given size.
     * The last chunk may be smaller than the specified size.
     *
     * This method is analogous to array_chunk().
     * @see https://www.php.net/manual/en/function.array-chunk.php
     *
     * @param int $size The size of each chunk.
     * @return self[] An array of Sequences representing the chunks.
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
     * Counts the occurrences of each distinct value in a Sequence.
     *
     * This method is analogous to array_count_values().
     * @see https://www.php.net/manual/en/function.array-count-values.php
     *
     * @return Dictionary A Dictionary mapping values to the number of occurrences.
     */
    public function countValues(): Dictionary
    {
        // Construct the dictionary.
        $value_count = new Dictionary($this->valueTypes, 'uint');

        // Count the occurrences of each distinct value.
        foreach ($this->items as $item) {
            $value_count[$item] = ($value_count[$item] ?? 0) + 1;
        }

        return $value_count;
    }

    /**
     * Fill the Sequence with a given value.
     *
     * This method is analogous to array_fill().
     * @see https://www.php.net/manual/en/function.array-fill.php
     *
     * @param int $start_index The zero-based index position to start filling.
     * @param int $count The number of items to fill.
     * @param mixed $value The value to fill with.
     * @return $this The calling object, for chaining.
     */
    public function fill(int $start_index, int $count, mixed $value): self
    {
        // Set the specified Sequence items.
        for ($i = 0; $i < $count; $i++) {
            $this[$start_index + $i] = $value;
        }

        return $this;
    }

    /**
     * Return a Sequence with all items matching a certain filter.
     *
     * This method is analogous to array_filter().
     * @see https://www.php.net/manual/en/function.array-filter.php
     *
     * @param callable $callback The filter function that returns true for items to keep.
     * @return self A new Sequence containing only the matching items.
     */
    #[Override]
    public function filter(callable $callback): static
    {
        // Get the matching values.
        $items = array_filter($this->items, $callback);

        // Construct the result.
        return $this->fromSubset($items);
    }

    /**
     * Applies the callback to the items in the Sequence.
     *
     * @param callable $fn The callback function to apply to each item.
     * @return self A new Sequence containing the results of the callback function.
     */
    public function map(callable $fn): self
    {
        // Apply the callback to each item.
        $items = array_map($fn, $this->items);

        // Construct the new Sequence, inferring types from the callback results.
        return new self(source: $items);
    }

    /**
     * Merge two sequences.
     *
     * @param Sequence $other The Sequence to merge with.
     * @return self A new Sequence containing the merged items.
     */
    public function merge(self $other): self
    {
        // Merge the two sequences.
        $items = array_merge($this->items, $other->items);

        // Construct the result.
        return $this->fromSubset($items);
    }

    /**
     * Return a new Sequence with the same items as the $this Sequence but in reverse order.
     *
     * @return self A new Sequence with the same items as the $this Sequence but in reverse order.
     */
    public function reverse(): self
    {
        // Reverse the items.
        $items = array_reverse($this->items);

        // Construct the result.
        return $this->fromSubset($items);
    }

    /**
     * Get the unique values from the Sequence.
     *
     * This method is analogous to array_unique().
     * @see https://www.php.net/manual/en/function.array-unique.php
     *
     * @return self A new Sequence containing the unique values.
     */
    public function unique(): self
    {
        // Get the unique values.
        $items = array_unique($this->items);

        // Construct the result.
        return $this->fromSubset($items);
    }

    // endregion

    // region Aggregation methods

    /**
     * Reduce the Sequence to a single value using a callback function.
     *
     * @param callable $fn Callback function (accumulator, item) => new_accumulator.
     * @param mixed $init Initial value for the aggregation.
     * @return mixed The final result.
     */
    public function reduce(callable $fn, mixed $init): mixed
    {
        // Manually iterate instead of using array_reduce() to ensure strict type checking on the callback parameters.
        $accumulator = $init;
        foreach ($this->items as $item) {
            $accumulator = $fn($accumulator, $item);
        }
        return $accumulator;
    }

    /**
     * Find the product of the values in the Sequence.
     *
     * @return int|float The product of the values in the Sequence.
     * @throws TypeError If the Sequence contains non-numeric values.
     */
    public function product(): int|float
    {
        // Use a custom reducer to find the product instead of array_product(), because that function will allow
        // non-numbers, and this one will not. It's more consistent with the type-safe philosophy of this
        // library to be strict.
        $prod = static fn(int|float $acc, int|float $item): int|float => $acc * $item;
        return $this->reduce($prod, 1);
    }

    /**
     * Find the sum of the values in the Sequence.
     *
     * @return int|float The sum of the values in the Sequence.
     * @throws TypeError If the Sequence contains non-numeric values.
     */
    public function sum(): int|float
    {
        // Use a custom reducer to find the product instead of array_product(), because that function will allow
        // non-numbers, and this one will not. It's more consistent with the type-safe philosophy of this
        // library to be strict.
        $sum = static fn(int|float $acc, int|float $item): int|float => $acc + $item;
        return $this->reduce($sum, 0);
    }

    /**
     * Find the minimum value in the Sequence.
     *
     * @return int|float The minimum value in the Sequence.
     * @throws UnderflowException If the Sequence is empty.
     */
    public function min(): int|float
    {
        // Check we have items.
        if ($this->empty()) {
            throw new UnderflowException("Cannot find the minimum value of empty Sequence.");
        }

        // Find the minimum value.
        return min($this->items);
    }

    /**
     * Find the maximum value in the Sequence.
     *
     * @return int|float The maximum value in the Sequence.
     * @throws UnderflowException If the Sequence is empty.
     */
    public function max(): int|float
    {
        // Check we have items.
        if ($this->empty()) {
            throw new UnderflowException("Cannot find the maximum value of empty Sequence.");
        }

        // Find the maximum value.
        return max($this->items);
    }

    /**
     * Find the average value in the Sequence.
     *
     * @return int|float The average value in the Sequence.
     * @throws UnderflowException If the Sequence is empty.
     */
    public function average(): int|float
    {
        // Check we have items.
        if ($this->empty()) {
            throw new UnderflowException("Cannot calculate the average value of empty Sequence.");
        }

        // Find the average value.
        return $this->sum() / $this->count();
    }

    /**
     * Find the concatenation of the values in the Sequence, optionally separated by a given string (the "glue").
     *
     * NB: This method is analogous to implode().
     * @see https://www.php.net/manual/en/function.implode.php
     *
     * It may generate an error or throw an exception if the Sequence contains an object that doesn't implement
     * __toString(). TODO - test.
     *
     * @param string $glue The string to separate the values with.
     * @return string The concatenation of the values in the Sequence.
     */
    public function join(string $glue = ''): string
    {
        return implode($glue, $this->items);
    }

    // endregion

    // region Random methods

    /**
     * Randomly choose one or more indexes from the Sequence.
     *
     * NB: This is a private helper method called from chooseRand() and removeRand(), and not part of the public API.
     *
     * @param int $count The number of indexes to choose (default: 1).
     * @return int[] An array containing the chosen indexes.
     * @throws OutOfRangeException If the Sequence is empty, or the count is non-positive, or the Sequence doesn't
     * have enough items to choose the requested number of items.
     */
    private function chooseRandIndexes(int $count = 1): array
    {
        // Guards.
        if ($this->empty()) {
            throw new OutOfRangeException("Cannot choose items from an empty Sequence.");
        }
        if ($count <= 0) {
            throw new OutOfRangeException("Count must be greater than 0.");
        }
        if ($count > $this->count()) {
            throw new OutOfRangeException("Cannot choose $count items from a Sequence with {$this->count()} items.");
        }

        // Randomly choose one or more indexes.
        $indexes = array_rand($this->items, $count);

        // Convert result to an array if necessary.
        // This is necessary because array_rand() will return a single value when $count is 1.
        if (!is_array($indexes)) {
            $indexes = [$indexes];
        }

        return $indexes;
    }

    /**
     * Randomly choose one or more items from the Sequence.
     *
     * Both indexes and values are returned, as an array.
     *
     * NB: This method is non-mutating.
     *
     * @param int $count The number of items to choose (default: 1).
     * @return array<int, mixed> An array containing the chosen items (indexes and values) in random order.
     * @throws OutOfRangeException If the Sequence is empty, the count is out of range or the Sequence doesn't
     * have enough items to choose the specified count.
     * @example
     * $seq = Sequence::range(1, 10);
     * $items = $seq->chooseRand(3);
     * // Returns: [2 => 3, 7 => 8, 5 => 6] (indexes and values in random order)
     *
     */
    public function chooseRand(int $count = 1): array
    {
        // Randomly choose one or more indexes.
        $indexes = $this->chooseRandIndexes($count);

        // Convert the indexes into index-value pairs.
        $result = [];
        foreach ($indexes as $i) {
            $result[$i] = $this->items[$i];
        }
        return $result;
    }

    /**
     * Randomly remove one or more items from the Sequence.
     *
     * NB: This method is mutating.
     *
     * @param int $count The number of items to remove (default: 1).
     * @return list<mixed> An array containing the removed values in random order.
     * @throws OutOfRangeException If the Sequence is empty, the count is out of range, or the Sequence doesn't have
     * enough items to remove the specified count.
     */
    public function removeRand(int $count = 1): array
    {
        // Randomly choose one or more indexes.
        $indexes = $this->chooseRandIndexes($count);

        // Work with a copy to preserve indexes during removal.
        $items = $this->items;

        // Remove the items from the Sequence.
        $removed = [];
        foreach ($indexes as $i) {
            // Remember the value.
            $removed[] = $items[$i];

            // Unset the value at this index.
            unset($items[$i]);
        }

        // Update the internal array of items.
        $this->items = array_values($items);

        // Return the values that were removed.
        return $removed;
    }

    // endregion

    // region ArrayAccess implementation

    /**
     * Check if a given index is valid.
     *
     * @param mixed $offset The Sequence index position.
     * @return bool If the given index is an integer and within the current valid range for the Sequence.
     * @throws TypeError If the index is not an integer.
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        // Check the index is an integer.
        if (!is_int($offset)) {
            throw Types::createError('offset', 'int', $offset);
        }

        return array_key_exists($offset, $this->items);
    }

    /**
     * Get a value from the Sequence.
     *
     * @param mixed $offset The zero-based index position to get.
     * @return mixed The value at the specified index.
     * @throws TypeError If the index is not an integer.
     * @throws OutOfRangeException If the index is outside the valid range for the Sequence.
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
     * Append or set a Sequence item.
     *
     * If the index is out of range, the Sequence will be increased in size to accommodate it.
     * Any intermediate positions will be filled with the default value.
     * NB: If the default is an object, all items set to the default will clone the object.
     * If you don't want this behaviour, don't rely on it; set each Sequence item individually.
     *
     * @param mixed $offset The zero-based index position to set, or null to append.
     * @param mixed $value The value to set.
     * @throws TypeError If the index is neither null nor an integer.
     * @throws OutOfRangeException If the index is negative.
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Check the item has a valid type.
        $this->valueTypes->check($value);

        if ($offset === null) {
            // Called from $sequence[] = $value

            // Append a new item to the Sequence.
            $this->append($value);
        }
        else {
            // Called from $sequence[$key] = $value

            // Check the index is valid.
            $this->checkIndex($offset, false);

            // Fill in any missing positions with defaults.
            $start = count($this->items);
            for ($i = $start; $i < $offset; $i++) {
                $this->items[$i] = $this->getDefaultValue();
            }

            // Set the item value.
            $this->items[$offset] = $value;
        }
    }

    /**
     * Reset a Sequence item to the default value (e.g. null, false, 0, 0.0, '', or []).
     *
     * This method isn't usually called as a method, but rather indirectly by calling unset($sequence[$offset]).
     *
     * Doing this doesn't remove an item from the Sequence, as it does with ordinary PHP arrays. This is because this
     * data structure maintains zero-indexed sequential keys. Therefore, removing an item from the Sequence would
     * require re-indexing later items. This could be unexpected behavior.
     *
     * To remove an item from the Sequence, use one of the remove*() methods.
     *
     * @param mixed $offset The zero-based index position to unset.
     * @throws TypeError If the index is not an integer.
     * @throws OutOfRangeException If the index is outside the valid range for the Sequence.
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        // Check the index is valid.
        $this->checkIndex($offset);

        // Set the item to the default value.
        $this->items[$offset] = $this->getDefaultValue();
    }

    // endregion

    // region Conversion methods

    /**
     * Convert the Sequence to a Dictionary. The Sequence's indexes will be keys in the new Dictionary.
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
     * Convert the Sequence to a Set. The resulting Set will contain only unique values from the Sequence.
     *
     * @return Set The new Set.
     */
    public function toSet(): Set
    {
        // Create the new Set.
        $set = new Set($this->valueTypes);

        // Add the values to the Set. Duplicates will be ignored.
        $set->add(...$this->items);

        // Return the new Set.
        return $set;
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
}
