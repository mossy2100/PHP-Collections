# Sequence

A type-specific list implementation with zero-based sequential integer indexing.

## Features

- Sequential integer indexes starting from 0
- Optional type constraints with runtime validation
- Automatic default values with gap-filling support
- Immutable and mutating operations
- ArrayAccess and iteration support
- Rich set of transformation and aggregation methods

## Constructor

### __construct()

```php
public function __construct(
    null|string|iterable|true $types = true,
    mixed $default_value = null,
    iterable $source = []
)
```

Create a new Sequence with optional type constraints, default value, and initial values from a source iterable.

**Type Constraints:**

The `$types` parameter accepts:
- `null` - Values of any type are allowed
- `string` - A type name, or multiple types using union type or nullable type syntax (e.g., `'string'`, `'int|null'`, `'?int'`)
- `iterable` - Array or other collection of type names (e.g., `['string', 'int']`)
- `true` (default) - Types will be inferred automatically from the source iterable's values

**Default Value Inference:**

If the default value is not specified, it will be automatically inferred from the type constraints:

- `null` or `mixed` → `null`
- `int`, `uint`, `number`, or `scalar` → `0`
- `float` → `0.0`
- `string` → `''` (empty string)
- `bool` → `false`
- `array` or `iterable` → `[]` (empty array)

For other types (classes, interfaces, traits, resources, callables), the default value will be `null`, and `'null'` will be automatically added to the allowed types if not already present.

**Examples:**
```php
// No type constraints, default is null
$seq = new Sequence();

// Single type constraint, default inferred
$seq = new Sequence('int');
echo $seq->defaultValue; // 0

// Union type constraint, default inferred
$seq = new Sequence('string|int');
echo $seq->defaultValue; // 0 (first suitable type)

// Array of types
$seq = new Sequence(['string', 'int']);

// With custom default value
$seq = new Sequence('string', 'default');
echo $seq->defaultValue; // 'default'

// Object type - null added automatically
$seq = new Sequence('DateTime');
echo $seq->defaultValue === null; // true
var_dump($seq->valueTypes->contains('null')); // true (auto-added)

// Object type with explicit default
$seq = new Sequence('DateTime', new DateTime());
echo $seq->defaultValue instanceof DateTime; // true

// Create from array with type inference (default)
$seq = new Sequence(source: [1, 2, 3, 4, 5]);
echo $seq->count(); // 5
// Types inferred as 'int', default value inferred as 0

// Create from array with explicit types
$seq = new Sequence('int', null, [1, 2, 3]);
echo $seq->count(); // 3

// Create with custom default and source
$seq = new Sequence('int', 99, [1, 2, 3]);
echo $seq->defaultValue; // 99

// Create from generator with type inference
$generator = function() {
    yield 10;
    yield 20;
    yield 30;
};
$seq = new Sequence(source: $generator());
echo $seq->count(); // 3
```

## Factory Methods

### range()

```php
public static function range(int|float $start, int|float $end, int|float $step = 1): self
```

Generate a Sequence of numbers spanning a given range. Throws `ValueError` for invalid step sizes.

**Examples:**
```php
// Ascending integers
$seq = Sequence::range(1, 10);        // [1, 2, 3, ..., 10]

// Descending integers
$seq = Sequence::range(10, 1, -1);    // [10, 9, 8, ..., 1]

// Floats with step
$seq = Sequence::range(0.0, 1.0, 0.2); // [0.0, 0.2, 0.4, ..., 1.0]
```

## Add Items to Sequence

### append()

```php
public function append(mixed ...$items): self
```

Add one or more items to the end of the Sequence. Returns `$this` for chaining. Throws `TypeError` for invalid item types.

**Examples:**
```php
$seq = new Sequence('int');
$seq->append(10);
$seq->append(20, 30, 40);
$seq->append(...[50, 60, 70]);

echo $seq->count(); // 7
```

### prepend()

```php
public function prepend(mixed ...$items): self
```

Add one or more items to the start of the Sequence. Returns `$this` for chaining.

Note the list of elements is prepended as a whole, so the prepended elements stay in the same order as they are supplied.

**Example:**
```php
$seq = new Sequence('string');
$seq->append('c', 'd');
$seq->prepend('a', 'b');

echo $seq[0]; // 'a' (first argument becomes first)
echo $seq[3]; // 'd'
```

### insert()

```php
public function insert(int $index, mixed $item): self
```

Insert an item at a specified position. Items after the index are shifted right. Fills gaps with default values if inserting beyond current range. Returns `$this` for chaining. Throws `OutOfRangeException` for negative indexes.

**Examples:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 4, 5);
$seq->insert(2, 3); // [1, 2, 3, 4, 5]

// Insert beyond end fills gaps
$seq = new Sequence('int');
$seq->append(1, 2);
$seq->insert(5, 10); // [1, 2, 0, 0, 0, 10]
```

### import()

```php
public function import(iterable $src): static
```

Import values from an iterable into the Sequence. Returns `$this` for chaining. Throws `TypeError` for invalid value types.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2);
$seq->import([3, 4, 5]);

echo $seq->count(); // 5
```

## Remove Items from Sequence

### clear()

```php
public function clear(): static
```

Remove all items from the Sequence. Returns `$this` for chaining.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 3);
$seq->clear();

echo $seq->count(); // 0
```

### removeByIndex()

```php
public function removeByIndex(int $index): mixed
```

Remove and return the item at a given index. Later items shift down. Throws `OutOfRangeException` for invalid indexes.

**Example:**
```php
$seq = new Sequence('string');
$seq->append('a', 'b', 'c', 'd');
$removed = $seq->removeByIndex(1); // 'b'

echo $seq[1]; // 'c' (shifted down)
echo $seq->count(); // 3
```

### removeByValue()

```php
public function removeByValue(mixed $value): int
```

Remove all items matching a given value (strict equality). Returns the number of items removed.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 3, 2, 4, 2);
$count = $seq->removeByValue(2); // 3

echo $seq->count(); // 3
```

### removeFirst()

```php
public function removeFirst(): mixed
```

Remove and return the first item. Throws `UnderflowException` if empty.

**Example:**
```php
$seq = new Sequence('string');
$seq->append('a', 'b', 'c');
$first = $seq->removeFirst(); // 'a'

echo $seq[0]; // 'b'
```

### removeLast()

```php
public function removeLast(): mixed
```

Remove and return the last item. Throws `UnderflowException` if empty.

**Example:**
```php
$seq = new Sequence('string');
$seq->append('a', 'b', 'c');
$last = $seq->removeLast(); // 'c'

echo $seq->count(); // 2
```

## Inspection Methods

### empty()

```php
public function empty(): bool
```

Check if the Sequence is empty.

**Example:**
```php
$seq = new Sequence('int');
echo $seq->empty() ? 'empty' : 'not empty'; // 'empty'

$seq->append(1);
echo $seq->empty() ? 'empty' : 'not empty'; // 'not empty'
```

### contains()

```php
public function contains(mixed $value): bool
```

Check if the Sequence contains a value (strict equality).

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 3);

var_dump($seq->contains(2));   // true
var_dump($seq->contains('2')); // false (strict)
```

### equals()

```php
public function equals(Collection $other): bool
```

Check if equal to another Collection. Collections must be same class, have same count, and same values in same order. Type constraints are ignored.

**Example:**
```php
$seq1 = new Sequence(source: [1, 2, 3]);
$seq2 = new Sequence(source: [1, 2, 3]);
$seq3 = new Sequence(source: [1, 2, 4]);

var_dump($seq1->equals($seq2)); // true
var_dump($seq1->equals($seq3)); // false
```

### indexExists()

```php
public function indexExists(int $index): bool
```

Check if an index exists in the Sequence.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 3);

var_dump($seq->indexExists(1)); // true
var_dump($seq->indexExists(5)); // false
```

### all()

```php
public function all(callable $fn): bool
```

Check if all items pass a test.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(2, 4, 6, 8);

var_dump($seq->all(fn($x) => $x % 2 === 0)); // true
var_dump($seq->all(fn($x) => $x > 5));       // false
```

### any()

```php
public function any(callable $fn): bool
```

Check if any item passes a test.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 3, 5, 6);

var_dump($seq->any(fn($x) => $x % 2 === 0)); // true
var_dump($seq->any(fn($x) => $x > 10));      // false
```

## Get Items from Sequence

### first()

```php
public function first(): mixed
```

Get the first item. Throws `OutOfRangeException` if empty.

**Example:**
```php
$seq = new Sequence('string');
$seq->append('a', 'b', 'c');
echo $seq->first(); // 'a'
```

### last()

```php
public function last(): mixed
```

Get the last item. Throws `OutOfRangeException` if empty.

**Example:**
```php
$seq = new Sequence('string');
$seq->append('a', 'b', 'c');
echo $seq->last(); // 'c'
```

### slice()

```php
public function slice(int $index, ?int $length = null): self
```

Get a slice of the Sequence. Supports negative indexes and lengths.

**Examples:**
```php
$seq = Sequence::range(1, 10);

// Get items 3-6
$slice = $seq->slice(3, 4); // [4, 5, 6, 7]

// Get last 3 items
$slice = $seq->slice(-3); // [8, 9, 10]

// Get items except last 2
$slice = $seq->slice(0, -2); // [1, 2, 3, 4, 5, 6, 7, 8]
```

### search()

```php
public function search(mixed $value): ?int
```

Search for a value and return its index, or null if not found (strict equality).

**Example:**
```php
$seq = new Sequence('string');
$seq->append('apple', 'banana', 'cherry');

echo $seq->search('banana'); // 1
var_dump($seq->search('grape')); // null
```

### find()

```php
public function find(callable $fn): mixed
```

Find the first element satisfying a callback function, or null if not found.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 3, 4, 6, 8);

$result = $seq->find(fn($x) => $x % 2 === 0); // 4
$result = $seq->find(fn($x) => $x > 10);      // null
```

## Sort Methods

### sort()

```php
public function sort(int $flags = SORT_REGULAR): self
```

Return a new Sequence with items sorted in ascending order (non-mutating).

**Example:**
```php
$seq = new Sequence('int');
$seq->append(5, 2, 8, 1, 9);
$sorted = $seq->sort();

echo $sorted[0]; // 1
echo $seq[0];    // 5 (original unchanged)
```

### sortReverse()

```php
public function sortReverse(int $flags = SORT_REGULAR): self
```

Return a new Sequence with items sorted in descending order (non-mutating).

**Example:**
```php
$seq = new Sequence('int');
$seq->append(5, 2, 8, 1, 9);
$sorted = $seq->sortReverse();

echo $sorted[0]; // 9
```

### sortBy()

```php
public function sortBy(callable $fn): self
```

Return a new Sequence sorted using a custom comparison function (non-mutating).

**Example:**
```php
$seq = new Sequence('int');
$seq->append(-5, 3, -1, 4, -2);

// Sort by absolute value
$sorted = $seq->sortBy(fn($a, $b) => abs($a) <=> abs($b));
echo $sorted[0]; // -1
```

## Transformation Methods

### filter()

```php
public function filter(callable $callback): static
```

Return a new Sequence containing only items that pass the test.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 3, 4, 5, 6);

$evens = $seq->filter(fn($x) => $x % 2 === 0);
echo $evens->count(); // 3
```

### map()

```php
public function map(callable $fn): self
```

Return a new Sequence with the callback applied to each item. Types are inferred from results.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 3);

$doubled = $seq->map(fn($x) => $x * 2);
echo $doubled[2]; // 6

$strings = $seq->map(fn($x) => "Number $x");
echo $strings[0]; // 'Number 1'
```

### merge()

```php
public function merge(self $other): self
```

Return a new Sequence with items from both Sequences.

**Example:**
```php
$seq1 = new Sequence(source: [1, 2, 3]);
$seq2 = new Sequence(source: [4, 5, 6]);
$merged = $seq1->merge($seq2);

echo $merged->count(); // 6
```

### reverse()

```php
public function reverse(): self
```

Return a new Sequence with items in reverse order (non-mutating).

**Example:**
```php
$seq = new Sequence(source: ['a', 'b', 'c']);
$reversed = $seq->reverse();

echo $reversed[0]; // 'c'
echo $seq[0];      // 'a' (unchanged)
```

### unique()

```php
public function unique(): self
```

Return a new Sequence containing only unique values.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 2, 3, 3, 3, 4);
$unique = $seq->unique();

echo $unique->count(); // 4
```

### chunk()

```php
public function chunk(int $size): array
```

Split the Sequence into chunks of a given size. Returns an array of Sequences.

**Example:**
```php
$seq = Sequence::range(1, 10);
$chunks = $seq->chunk(3);

echo count($chunks);     // 4
echo $chunks[0]->count(); // 3
echo $chunks[3]->count(); // 1 (remainder)
```

### fill()

```php
public function fill(int $start_index, int $count, mixed $value): self
```

Fill a portion of the Sequence with a value. Returns `$this` for chaining.

**Examples:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 3, 4, 5);
$seq->fill(1, 3, 99); // [1, 99, 99, 99, 5]

// Fill with a specific value
$seq = new Sequence('int', 0);
$seq->fill(0, 5, 7); // [7, 7, 7, 7, 7]
```

## Aggregation Methods

### count()

```php
public function count(): int
```

Get the number of items in the Sequence.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 3);
echo $seq->count(); // 3
echo count($seq);   // 3 (Countable)
```

### reduce()

```php
public function reduce(callable $fn, mixed $init): mixed
```

Reduce the Sequence to a single value using a callback function.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 3, 4, 5);

$sum = $seq->reduce(fn($acc, $x) => $acc + $x, 0);
echo $sum; // 15
```

### product()

```php
public function product(): float|int
```

Calculate the product of all values.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(2, 3, 4);
echo $seq->product(); // 24
```

### sum()

```php
public function sum(): float|int
```

Calculate the sum of all values.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 3, 4, 5);
echo $seq->sum(); // 15
```

### min()

```php
public function min(): int|float
```

Find the minimum value in the Sequence. Throws `UnderflowException` if empty.

**Examples:**
```php
$seq = new Sequence('int');
$seq->append(5, 2, 8, 1, 9, 3);
echo $seq->min(); // 1

// Works with floats
$seq = new Sequence('float');
$seq->append(3.14, 2.71, 1.41, 4.67);
echo $seq->min(); // 1.41

// Works with negative numbers
$seq = new Sequence('int');
$seq->append(5, -2, 8, -10, 3);
echo $seq->min(); // -10
```

### max()

```php
public function max(): int|float
```

Find the maximum value in the Sequence. Throws `UnderflowException` if empty.

**Examples:**
```php
$seq = new Sequence('int');
$seq->append(5, 2, 8, 1, 9, 3);
echo $seq->max(); // 9

// Works with floats
$seq = new Sequence('float');
$seq->append(3.14, 2.71, 1.41, 4.67);
echo $seq->max(); // 4.67

// Works with negative numbers
$seq = new Sequence('int');
$seq->append(-5, -2, -8, -1, -3);
echo $seq->max(); // -1
```

### average()

```php
public function average(): int|float
```

Calculate the average (arithmetic mean) of all values. Throws `UnderflowException` if empty.

**Examples:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 3, 4, 5);
echo $seq->average(); // 3

// Works with floats
$seq = new Sequence('float');
$seq->append(1.5, 2.5, 3.5, 4.5);
echo $seq->average(); // 3.0

// Single item returns that item
$seq = new Sequence('int');
$seq->append(42);
echo $seq->average(); // 42
```

### join()

```php
public function join(string $glue = ''): string
```

Concatenate values into a string, optionally separated by glue.

**Examples:**
```php
$seq = new Sequence('string');
$seq->append('apple', 'banana', 'cherry');

echo $seq->join();       // 'applebananacherry'
echo $seq->join(', ');   // 'apple, banana, cherry'
```

### countValues()

```php
public function countValues(): Dictionary
```

Count occurrences of each distinct value. Returns a Dictionary mapping values to counts.

**Example:**
```php
$seq = new Sequence('string');
$seq->append('a', 'b', 'a', 'c', 'a', 'b');
$counts = $seq->countValues();

echo $counts['a']; // 3
echo $counts['b']; // 2
echo $counts['c']; // 1
```

## Random Methods

### chooseRand()

```php
public function chooseRand(int $count = 1): array
```

Randomly choose one or more items from the Sequence (non-mutating). Returns an associative array with indexes as keys and values. Throws `OutOfRangeException` if empty, or count is invalid.

**Example:**
```php
$seq = Sequence::range(1, 10);
$chosen = $seq->chooseRand(3);

// Example result: [2 => 3, 7 => 8, 4 => 5]
echo count($chosen); // 3
```

### removeRand()

```php
public function removeRand(int $count = 1): array
```

Randomly remove one or more items from the Sequence (mutating). Returns a list of removed values. Throws `OutOfRangeException` if empty, or count is invalid.

**Example:**
```php
$seq = Sequence::range(1, 10);
$removed = $seq->removeRand(2);

echo count($removed);   // 2
echo $seq->count();     // 8
```

## ArrayAccess Implementation

Sequences support array-like access with square brackets:

```php
$seq = new Sequence('int');

// Append with []
$seq[] = 10;
$seq[] = 20;

// Set at index
$seq[0] = 15;

// Get value
echo $seq[1]; // 20

// Check existence
var_dump(isset($seq[1])); // true

// Unset sets to default value (doesn't remove)
unset($seq[0]);
echo $seq[0]; // 0 (default for int)

// Setting beyond range fills gaps
$seq[5] = 99; // Indexes 2-4 filled with defaults
```

## Iteration

Sequences support `foreach` iteration:

```php
$seq = new Sequence('string');
$seq->append('a', 'b', 'c');

foreach ($seq as $value) {
    echo $value; // 'a', 'b', 'c'
}

foreach ($seq as $index => $value) {
    echo "$index: $value\n";
    // 0: a
    // 1: b
    // 2: c
}
```

## Conversion Methods

### toArray()

```php
public function toArray(): array
```

Convert the Sequence to a plain PHP array.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 3);
$array = $seq->toArray();

print_r($array); // [1, 2, 3]
```

### toDictionary()

```php
public function toDictionary(): Dictionary
```

Convert to a Dictionary. Sequence indexes become Dictionary keys.

**Example:**
```php
$seq = new Sequence('string');
$seq->append('apple', 'banana', 'cherry');
$dict = $seq->toDictionary();

echo $dict[0]; // 'apple'
echo $dict[1]; // 'banana'
```

### toSet()

```php
public function toSet(): Set
```

Convert to a Set. Duplicate values are removed.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 2, 3, 3, 3);
$set = $seq->toSet();

echo $set->count(); // 3
```

### __toString()

```php
public function __toString(): string
```

Convert the Sequence to a string representation.

**Example:**
```php
$seq = new Sequence('int');
$seq->append(1, 2, 3);
echo $seq; // String representation
```

## Usage Examples

### Type-safe integer list

```php
$numbers = new Sequence('int');
$numbers->append(1, 2, 3, 4, 5);

// Safe operations
$doubled = $numbers->map(fn($x) => $x * 2);
$sum = $numbers->sum();
echo "Sum: $sum"; // 15

// This would throw TypeError
// $numbers->append('string');
```

### Working with objects

```php
$dates = new Sequence('DateTime', new DateTime());
$dates->append(new DateTime('2025-01-01'));
$dates->append(new DateTime('2025-06-01'));
$dates->append(new DateTime('2025-12-31'));

// Sort by date
$sorted = $dates->sortBy(fn($a, $b) => $a <=> $b);
```

### Data processing pipeline

```php
$data = new Sequence(source: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

$result = $data
    ->filter(fn($x) => $x % 2 === 0)  // [2, 4, 6, 8, 10]
    ->map(fn($x) => $x * $x)          // [4, 16, 36, 64, 100]
    ->slice(0, 3);                    // [4, 16, 36]

echo $result->sum(); // 56
```

### Gap-filling with defaults

```php
$seq = new Sequence('int', 0);
$seq[10] = 99;

// Indexes 0-9 are filled with 0
echo $seq[5];  // 0
echo $seq[10]; // 99
echo $seq->count(); // 11
```

### Merging sequences

```php
$seq1 = new Sequence(source: ['a', 'b', 'c']);
$seq2 = new Sequence(source: ['d', 'e', 'f']);
$combined = $seq1->merge($seq2);

echo $combined->join('-'); // 'a-b-c-d-e-f'
```

### Statistical operations

```php
$scores = new Sequence('int');
$scores->append(85, 92, 78, 95, 88, 91);

$average = $scores->sum() / $scores->count();
$sorted = $scores->sort();
$median = $sorted[$scores->count() / 2];

echo "Average: $average"; // 88.17
echo "Median: $median";   // 88.5
```
