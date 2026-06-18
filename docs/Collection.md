# Collection

The abstract base class for all collection types in the OceanMoon Collections library.

---

## Overview

Collection provides the foundation for Sequence, Dictionary, and Set by implementing common functionality and defining the contract that all concrete collections must follow.

While you cannot instantiate Collection directly, understanding its API helps you know what methods are available on all collection types and what behavior they share.

**Implemented Interfaces:**

- **Countable** - All collections support `count()`
- **IteratorAggregate** - All collections support `foreach` loops
- **Stringable** - All collections can be converted to strings with `__toString()`

### Key Features

- Consistent API across all collection types (`count()`, `empty()`, `clear()`, `all()`, `any()`)
- Type safety through the `valueTypes` property using TypeSet for runtime validation
- Standard PHP interfaces for seamless integration with existing code
- Code reuse with common functionality implemented once
- Polymorphism allowing collections to be used interchangeably where only Collection methods are needed

---

## Properties

### items

```php
protected array $items = []
```

Internal array storage for collection items. All concrete collections use this array to store their data.

**Access:** Protected (only accessible within collection classes)

### valueTypes

```php
protected(set) TypeSet $valueTypes
```

TypeSet managing allowed value types for the collection. Handles runtime type validation.

**Access:** Protected set (can only be set within collection classes), public read (accessible via `$collection->valueTypes`)

**Example:**
```php
$seq = new Sequence('int|string');

// Read the types
echo $seq->valueTypes; // {int, string}

// Check types
var_dump($seq->valueTypes->contains('int'));    // true
var_dump($seq->valueTypes->contains('bool'));   // false
```

---

## Constructor

### \_\_construct()

```php
public function __construct(null|string|iterable $types = null)
```

Create a new Collection with optional type constraints.

**Parameters:**
- `$types` (null|string|iterable) - Type constraints (null for any type, string for single/union/nullable types, iterable for array of types)

**Throws:**
- `InvalidArgumentException` - If a type is not specified as a string
- `DomainException` - If a type name is invalid

**Note:** This is called by concrete collection constructors. You cannot instantiate Collection directly.

---

## Abstract Methods

These methods must be implemented by concrete collection classes (Sequence, Dictionary, Set).

### import()

```php
abstract public function import(iterable $source): static
```

Import values from an iterable into the Collection. Returns `$this` for chaining.

**Must throw:** `InvalidArgumentException` if any values have disallowed types.

**Implementations:**
- **Sequence:** Appends values to the end
- **Dictionary:** Adds key-value pairs
- **Set:** Adds values (duplicates ignored)

### contains()

```php
abstract public function contains(mixed $value): bool
```

Check if the Collection contains a specific value. Uses strict equality (value and type must match).

**Implementations:**
- **Sequence:** Checks if value exists in the list
- **Dictionary:** Checks if a value exists (not a key)
- **Set:** Checks if value exists in the set

### equal()

```php
abstract public function equal(mixed $other): bool
```

Check if two Collections are equal. The definition of "equal" varies by collection type. This method comes from the [Equatable](https://github.com/mossy2100/PHP-Core/blob/main/docs/Traits/Comparison/Equatable.md) trait.

**Implementations:**
- **Sequence:** Same type, count, values, and order
- **Dictionary:** Same type, count, keys, and values
- **Set:** Same type, count, and values (order doesn't matter)

### filter()

```php
abstract public function filter(callable $callback): static
```

Filter the Collection using a callback function. Returns a new Collection with only items where the callback returns `true`.

**Implementations:**
- **Sequence:** Filters values, maintains order
- **Dictionary:** Filters key-value pairs
- **Set:** Filters values

### getIterator()

```php
abstract public function getIterator(): Traversable
```

Get an iterator for `foreach` loops. Implements the `IteratorAggregate` interface.

**Implementations:**
- **Sequence:** Iterates values with integer keys
- **Dictionary:** Iterates keys and values
- **Set:** Iterates values with auto-generated integer keys

---

## Modification Methods

### clear()

```php
public function clear(): static
```

Remove all items from the Collection. Returns `$this` for chaining.

**Example:**
```php
$seq = new Sequence('int', [1, 2, 3, 4, 5]);
echo $seq->count(); // 5

$seq->clear();
echo $seq->count(); // 0

// Chaining
$seq->clear()->append(10, 20, 30);
echo $seq->count(); // 3
```

---

## Inspection Methods

### empty()

```php
public function empty(): bool
```

Check if the Collection is empty (contains no items).

**Example:**
```php
$seq = new Sequence('int');
var_dump($seq->empty()); // true

$seq->append(1);
var_dump($seq->empty()); // false
```

### all()

```php
public function all(callable $fn): bool
```

Check if all items in the Collection pass a test function. Returns `true` if all items pass, `false` otherwise.

Analogous to PHP's `array_all()`.

**Callback signature:** `fn(mixed $item): bool`

**Important for Dictionary:** The callback receives `Pair` objects, not individual keys/values.

**Examples:**
```php
// Sequence: check if all numbers are positive
$seq = new Sequence('int', [1, 2, 3, 4, 5]);
var_dump($seq->all(fn($n) => $n > 0)); // true
var_dump($seq->all(fn($n) => $n > 3)); // false

// Dictionary: callback receives Pair objects
$dict = new Dictionary('string', 'int', ['a' => 1, 'b' => 2]);
var_dump($dict->all(fn($pair) => $pair->value > 0)); // true
```

### any()

```php
public function any(callable $fn): bool
```

Check if any items in the Collection pass a test function. Returns `true` if at least one item passes, `false` if none pass.

Analogous to PHP's `array_any()`.

**Callback signature:** `fn(mixed $item): bool`

**Important for Dictionary:** The callback receives `Pair` objects, not individual keys/values.

**Examples:**
```php
// Sequence: check if any number is even
$seq = new Sequence('int', [1, 3, 5, 6, 7]);
var_dump($seq->any(fn($n) => $n % 2 === 0)); // true (6 is even)

// Set: check if any string starts with 'a'
$set = new Set('string', ['apple', 'banana', 'cherry']);
var_dump($set->any(fn($s) => str_starts_with($s, 'a'))); // true
```

### count()

```php
public function count(): int
```

Get the number of items in the Collection. Implements the `Countable` interface.

**Example:**
```php
$seq = new Sequence('int', [1, 2, 3]);
echo $seq->count(); // 3

// Also works with count() function
echo count($seq); // 3
```

---

## Conversion Methods

### toArray()

```php
public function toArray(): array
```

Convert the Collection to a plain PHP array. Returns the internal `$items` array with integer keys starting from 0.

**Note:** For Dictionary, this returns an array of `Pair` objects, not an associative array.

**Example:**
```php
$seq = new Sequence('int', [1, 2, 3]);
$array = $seq->toArray();
var_dump($array); // [1, 2, 3]
```

### \_\_toString()

```php
public function __toString(): string
```

Convert the Collection to a string representation. Implements the `Stringable` interface.

**Examples:**
```php
$seq = new Sequence('int', [1, 2, 3]);
echo $seq; // [1, 2, 3]

$set = new Set('string', ['a', 'b', 'c']);
echo $set; // {a, b, c}
```

---

## Iteration

All collections support `foreach` through the `IteratorAggregate` interface:

```php
foreach ($collection as $value) {
    // Process each value
}

// With keys
foreach ($collection as $key => $value) {
    // Process key and value
}
```

The exact iteration behavior depends on the concrete collection type. See the documentation for Sequence, Dictionary, and Set for details.

---

## Extending Collection

To create your own collection type, extend the Collection class and implement the abstract methods:

```php
use OceanMoon\Collections\Collection;

class MyCustomCollection extends Collection
{
    public function import(iterable $source): static
    {
        // Implementation
        return $this;
    }

    public function contains(mixed $value): bool
    {
        // Implementation
        return false;
    }

    public function equal(mixed $other): bool
    {
        // Implementation
        return false;
    }

    public function filter(callable $callback): static
    {
        // Implementation
        return new static();
    }

    public function getIterator(): Traversable
    {
        // Implementation
        return new ArrayIterator($this->items);
    }
}
```

---

## See Also

- **[Sequence](Sequence.md)** - Ordered list implementation
- **[Dictionary](Dictionary.md)** - Key-value collection
- **[Pair](Pair.md)** - Key-value pair container used by Dictionary
- **[Set](Set.md)** - Unique values implementation
- **[TypeSet](TypeSet.md)** - Type constraint management
- **[Equatable](https://github.com/mossy2100/PHP-Core/blob/main/docs/Traits/Comparison/Equatable.md)** - Trait for implementing `equal()`
