# Dictionary

A type-safe key-value collection that accepts any PHP type for both keys and values.

---

## Overview

Dictionary provides a flexible key-value store that overcomes PHP array limitations. While PHP arrays only accept `string` or `int` keys, Dictionary lets you use values of any type.

### Key Features

- **Any type for keys** - Objects, arrays, resources, scalars, null - everything works
- **Type constraints** - Optional runtime validation for both keys and values
- **ArrayAccess** - Use familiar `$dict[$key]` syntax
- **Type inference** - Automatically detect types from data
- **Transformation methods** - filter, flip, map, merge, sort
- **Iteration** - Full foreach support with original key types preserved

```php
$dict = new Dictionary();
$dict[new DateTime()] = 'event';        // Object keys
$dict[[1, 2, 3]] = 'coordinates';       // Array keys
$dict[fopen('file.txt', 'r')] = 'data'; // Resource keys
$dict[true] = 'yes';                    // Boolean keys
$dict[null] = 'empty';                  // Null key
```

---

## Properties

### items

Inherited from [Collection](Collection.md#items). Internal array storage for the dictionary's Pair objects, keyed by the unique string representation of each Pair's key.

### valueTypes

Inherited from [Collection](Collection.md#valueTypes). TypeSet managing allowed value types for the Dictionary.

### keyTypes

```php
protected(set) TypeSet $keyTypes
```

TypeSet managing allowed key types for the Dictionary. Handles runtime type validation for keys.

**Access:** Protected set (can only be set within the class), public read (accessible via `$dict->keyTypes`)

**Example:**
```php
$dict = new Dictionary('string|int', 'mixed');

echo $dict->keyTypes; // {string, int}
var_dump($dict->keyTypes->contains('string')); // true
var_dump($dict->keyTypes->contains('array'));  // false
```

### keys

```php
public array $keys { get }
```

Computed property that returns all keys as an array.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2, 'c' => 3]);
$keys = $dict->keys; // ['a', 'b', 'c']
```

### values

```php
public array $values { get }
```

Computed property that returns all values as an array.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2, 'c' => 3]);
$values = $dict->values; // [1, 2, 3]
```

---

## Constructor

### \_\_construct()

```php
public function __construct(
    null|string|iterable|true $keyTypes = true,
    null|string|iterable|true $valueTypes = true,
    iterable $source = []
)
```

Create a Dictionary with optional type constraints and initial key-value pairs from a source iterable.

**Type Constraints:**

The `$keyTypes` and `$valueTypes` parameters accept:
- `null` - Any type allowed (no constraints)
- `string` - A type name, or multiple types using union type or nullable type syntax (e.g., `'int'`, `'int|string'`, `'?int'`)
- `iterable` - Array or other collection of type names (e.g., `['int', 'string']`)
- `true` (default) - Types will be inferred automatically from the source iterable's keys and values

**Note:** String union syntax and array syntax cannot be combined. Use `'int|string'` OR `['int', 'string']`, not `['int|string', 'float']`.

**Examples:**
```php
// No type constraints, empty Dictionary
$dict = new Dictionary();

// String keys, int values
$dict = new Dictionary('string', 'int');

// Union types using | syntax
$dict = new Dictionary('int|string', 'float|bool');

// Union types using array syntax (equivalent)
$dict = new Dictionary(['int', 'string'], ['float', 'bool']);

// DateTime keys, Customer values
$dict = new Dictionary('DateTime', 'Customer');

// Nullable values
$dict = new Dictionary('string', '?int');

// Create from array with type inference (default)
$dict = new Dictionary(source: ['a' => 1, 'b' => 2, 'c' => 3]);
echo $dict->count(); // 3
// Types inferred: keyTypes = 'string', valueTypes = 'int'

// Create from array with explicit types
$dict = new Dictionary('string', 'int', ['a' => 1, 'b' => 2]);

// Mixed types - infers union types
$dict = new Dictionary(source: [1 => 'one', 'two' => 2, 3 => true]);
// Types inferred: keyTypes = 'int|string', valueTypes = 'string|int|bool'

// No type constraints (any type allowed)
$dict = new Dictionary(null, null, ['a' => 1, 'b' => 'text']);

// From generator with type inference
$generator = function() {
    yield 'a' => 10;
    yield 'b' => 20;
};
$dict = new Dictionary(source: $generator());
```

---

## Factory Methods

### combine()

```php
public static function combine(
    iterable $keys,
    iterable $values,
    bool $inferTypes = true
): self
```

Create a new Dictionary by combining separate iterables of keys and values.

**Parameters:**
- `$keys` (iterable) - Iterable of keys
- `$values` (iterable) - Iterable of values
- `$inferTypes` (bool) - Whether to infer key and value types from the data (default: `true`)

**Returns:** A new Dictionary with the combined keys and values.

**Throws:**
- `LengthException` - If the iterables have different counts
- `OutOfBoundsException` - If keys are not unique

**Examples:**
```php
// Basic usage with type inference
$keys = ['name', 'age', 'email'];
$values = ['Alice', 30, 'alice@example.com'];
$dict = Dictionary::combine($keys, $values);
// Types inferred: keyTypes = 'string', valueTypes = 'string|int'

// With object keys
$date1 = new DateTime('2024-01-01');
$date2 = new DateTime('2024-02-01');
$dict = Dictionary::combine([$date1, $date2], ['New Year', 'February']);
echo $dict[$date1]; // 'New Year'

// Disable type inference for maximum flexibility
$dict = Dictionary::combine(['a', 'b'], [1, 2], false);
// No type constraints - can add any types later
$dict->add(123, 'text'); // Works

// Error: Mismatched counts
$dict = Dictionary::combine(['a', 'b', 'c'], [1, 2]);
// LengthException: Cannot combine: keys count (3) does not match values count (2).

// Error: Duplicate keys
$dict = Dictionary::combine(['a', 'b', 'a'], [1, 2, 3]);
// OutOfBoundsException: Cannot combine: keys are not unique.
```

## Modification Methods

### add()

```php
public function add(mixed $key, mixed $value): self
// OR
public function add(Pair $pair): self
```

Add a key-value pair. Returns `$this` for chaining.

**Throws:**
- `InvalidArgumentException` - If the key or value has a disallowed type
- `ArgumentCountError` - If the wrong number of parameters is supplied

**Examples:**
```php
$dict = new Dictionary('string', 'int');
$dict->add('count', 42);
$dict->add('total', 100);

// Using Pair
$dict->add(new Pair('average', 71));

// Chaining
$dict->add('min', 10)->add('max', 90);
```

### import()

```php
public function import(iterable $source): static
```

Import multiple key-value pairs from an iterable. Returns `$this` for chaining.

**Throws:** `InvalidArgumentException` - If any keys or values have disallowed types.

**Example:**
```php
$dict = new Dictionary('string', 'int');
$dict->import(['a' => 1, 'b' => 2, 'c' => 3]);
```

### removeByKey()

```php
public function removeByKey(mixed $key): mixed
```

Remove an item by key. Returns the value of the removed item.

**Throws:**
- `InvalidArgumentException` - If the key has a disallowed type
- `OutOfBoundsException` - If the key does not exist

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2, 'c' => 3]);
$value = $dict->removeByKey('b');
echo $value; // 2
echo $dict->count(); // 2
```

### removeByValue()

```php
public function removeByValue(mixed $value): int
```

Remove all items with a matching value. Returns the count of items removed.

**Throws:** `InvalidArgumentException` - If the value has a disallowed type.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2, 'c' => 1]);
$count = $dict->removeByValue(1);
echo $count; // 2 (removed 'a' and 'c')
echo $dict->count(); // 1 (only 'b' remains)
```

### clear()

```php
public function clear(): static
```

Remove all items from the Dictionary. Returns `$this` for chaining.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2]);
$dict->clear();
echo $dict->count(); // 0
```

## Inspection Methods

### contains()

```php
public function contains(mixed $value): bool
```

Check if the Dictionary contains a value (strict equality).

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2]);
var_dump($dict->contains(1));   // true
var_dump($dict->contains('1')); // false (strict equality)
var_dump($dict->contains(3));   // false
```

### keyExists()

```php
public function keyExists(mixed $key): bool
```

Check if a key exists in the Dictionary.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2]);
var_dump($dict->keyExists('a')); // true
var_dump($dict->keyExists('c')); // false
```

### empty()

```php
public function empty(): bool
```

Check if the Dictionary is empty.

**Example:**
```php
$dict = new Dictionary();
var_dump($dict->empty()); // true

$dict->add('key', 'value');
var_dump($dict->empty()); // false
```

### count()

```php
public function count(): int
```

Get the number of key-value pairs. Implements the `Countable` interface.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2, 'c' => 3]);
echo $dict->count(); // 3
echo count($dict);   // 3 (Countable interface)
```

### equal()

```php
public function equal(mixed $other): bool
```

Check if two Dictionaries are equal. Dictionaries are equal if they have the same type, count, keys, values, and order. Type constraints are not considered.

**Example:**
```php
$dict1 = new Dictionary(source: ['a' => 1, 'b' => 2]);
$dict2 = new Dictionary(source: ['a' => 1, 'b' => 2]);
$dict3 = new Dictionary(source: ['b' => 2, 'a' => 1]); // Different order

var_dump($dict1->equal($dict2)); // true
var_dump($dict1->equal($dict3)); // false (order matters)
```

### all()

```php
public function all(callable $fn): bool
```

Check if all items pass a test. The callback receives `Pair` objects.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2, 'c' => 3]);
var_dump($dict->all(fn($pair) => $pair->value > 0)); // true
var_dump($dict->all(fn($pair) => $pair->value > 2)); // false
```

### any()

```php
public function any(callable $fn): bool
```

Check if any item passes a test. The callback receives `Pair` objects.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2, 'c' => 3]);
var_dump($dict->any(fn($pair) => $pair->value > 2)); // true
var_dump($dict->any(fn($pair) => $pair->value > 5)); // false
```

## Sorting Methods

### sort()

```php
public function sort(callable $fn): self
```

Sort by custom comparison function (mutating). Returns `$this` for chaining.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 30, 'b' => 10, 'c' => 20]);
$dict->sort(fn($a, $b) => $a->value <=> $b->value);
// Order by value: 10, 20, 30
```

### sortByKey()

```php
public function sortByKey(): self
```

Sort by keys using the spaceship operator (mutating). Returns `$this` for chaining.

**Example:**
```php
$dict = new Dictionary(source: ['z' => 1, 'a' => 2, 'm' => 3]);
$dict->sortByKey();
// Order: a, m, z
```

### sortByValue()

```php
public function sortByValue(): self
```

Sort by values using the spaceship operator (mutating). Returns `$this` for chaining.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 30, 'b' => 10, 'c' => 20]);
$dict->sortByValue();
// Order by value: 10, 20, 30
```

## Transformation Methods

### filter()

```php
public function filter(callable $callback): static
```

Return a new Dictionary with items that pass the test. The callback receives a Pair argument and must return a bool.

**Throws:** `UnexpectedValueException` - If the callback doesn't return a bool.

**Examples:**
```php
$dict = new Dictionary(source: [
    'apple' => 5,
    'banana' => 3,
    'cherry' => 8
]);

// Filter by value
$expensive = $dict->filter(fn($pair) => $pair->value > 4);
// Result: ['apple' => 5, 'cherry' => 8]

// Filter by key
$aFruits = $dict->filter(fn($pair) => str_starts_with($pair->key, 'a'));
// Result: ['apple' => 5]
```

### flip()

```php
public function flip(): self
```

Swap keys with values. Returns a new Dictionary. All values must be unique.

**Throws:** `OutOfBoundsException` - If the Dictionary contains duplicate values.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2, 'c' => 3]);
$flipped = $dict->flip();
// Result: [1 => 'a', 2 => 'b', 3 => 'c']

// Duplicate values cause an error
$dict = new Dictionary(source: ['a' => 1, 'b' => 1]);
$flipped = $dict->flip(); // OutOfBoundsException
```

### map()

```php
public function map(callable $fn): self
```

Transform each key-value pair. The callback receives a `Pair` and must return a new `Pair`. Types are automatically inferred from results.

**Throws:**
- `UnexpectedValueException` - If the callback doesn't return a Pair
- `OutOfBoundsException` - If the callback produces duplicate keys

**Examples:**
```php
$dict = new Dictionary(source: [
    'apple' => 5,
    'banana' => 3
]);

// Double all values
$doubled = $dict->map(fn($pair) => new Pair($pair->key, $pair->value * 2));
// Result: ['apple' => 10, 'banana' => 6]

// Transform keys to uppercase
$upper = $dict->map(fn($pair) => new Pair(strtoupper($pair->key), $pair->value));
// Result: ['APPLE' => 5, 'BANANA' => 3]

// Swap keys and values
$swapped = $dict->map(fn($pair) => new Pair($pair->value, $pair->key));
// Result: [5 => 'apple', 3 => 'banana']
```

### merge()

```php
public function merge(self $other): self
```

Return a new Dictionary with items from both. Duplicate keys keep the value from the second Dictionary.

**Example:**
```php
$dict1 = new Dictionary(source: ['a' => 1, 'b' => 2]);
$dict2 = new Dictionary(source: ['b' => 20, 'c' => 3]);
$merged = $dict1->merge($dict2);
// Result: ['a' => 1, 'b' => 20, 'c' => 3]
```

## Conversion Methods

### \_\_toString()

```php
public function __toString(): string
```

Convert to a string representation. Implements the `Stringable` interface.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2]);
echo $dict; // String representation of the Dictionary
```

### toArray()

```php
public function toArray(): array
```

Convert to an array of `Pair` objects.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2]);
$pairs = $dict->toArray();
// Array of Pair objects, not ['a' => 1, 'b' => 2]
```

### toSequence()

```php
public function toSequence(): Sequence
```

Convert to a Sequence of `Pair` objects.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2]);
$seq = $dict->toSequence();
echo $seq->count(); // 2
```

## ArrayAccess

Dictionary implements `ArrayAccess` for familiar array-style syntax:

```php
$dict = new Dictionary('string', 'int');

// Set
$dict['count'] = 42;

// Get
echo $dict['count']; // 42

// Check existence
if (isset($dict['count'])) { ... }

// Unset
unset($dict['count']);
```

**Important:** Using `$dict[] = $value` syntax uses `null` as the key (not append like arrays).

## Iteration

Dictionary supports `foreach` with original key types preserved:

```php
$dict = new Dictionary(source: ['name' => 'Alice', 'age' => 30]);

foreach ($dict as $key => $value) {
    echo "$key: $value\n";
}
// Output:
// name: Alice
// age: 30
```

## Usage Examples

### Type-safe configuration

```php
$config = new Dictionary('string', 'int|string|bool');
$config['debug'] = true;
$config['maxItems'] = 100;
$config['apiKey'] = 'abc123';

// This would throw InvalidArgumentException:
// $config['timeout'] = 3.14; // float not allowed
```

### Object keys for caching

```php
$cache = new Dictionary('DateTime', 'string');
$timestamp = new DateTime('2025-01-01');
$cache[$timestamp] = 'New Year Data';

echo $cache[$timestamp]; // 'New Year Data'
```

### Data transformation pipeline

```php
$sales = new Dictionary(source: [
    'Product A' => 1500,
    'Product B' => 3200,
    'Product C' => 800
]);

// Filter and sort
$highValue = $sales->filter(fn($pair) => $pair->value > 1000);
$highValue->sortByValue();

// Transform to formatted strings
$formatted = $sales->map(fn($pair) => new Pair(
    $pair->key,
    "$" . number_format($pair->value)
));
// Result: ['Product A' => '$1,500', ...]
```

## Comparison with PHP Alternatives

### Dictionary vs WeakMap

| Feature            | Dictionary | WeakMap               |
|--------------------|------------|-----------------------|
| Key types          | Any type   | Objects only          |
| Type safety        | Yes        | No                    |
| ArrayAccess        | Yes        | No                    |
| Garbage collection | Manual     | Automatic (weak refs) |

**Use WeakMap when:** You need object keys with automatic cleanup.

**Use Dictionary when:** You need flexible key types, type safety, or non-object keys.

### Dictionary vs SplObjectStorage

| Feature     | Dictionary   | SplObjectStorage |
|-------------|--------------|------------------|
| Key types   | Any type     | Objects only     |
| Type safety | Yes          | No               |
| API style   | Array syntax | attach/detach    |
| PHP version | 8.4+         | 5.x+             |

**Use Dictionary when:** Building modern PHP 8.4+ applications with type safety needs.

## See Also

- **[Collection](Collection.md)** - Abstract base class
- **[Pair](Pair.md)** - Key-value pair container used by Dictionary
- **[Sequence](Sequence.md)** - Ordered list collection
- **[Set](Set.md)** - Unique value collection
- **[TypeSet](TypeSet.md)** - Type constraint management
- **[Equatable](https://github.com/mossy2100/PHP-Core/blob/main/docs/Traits/Comparison/Equatable.md)** - Trait for implementing `equal()`
