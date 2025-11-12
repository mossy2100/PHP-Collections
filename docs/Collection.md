# Collection

The abstract base class for all collection types in the Galaxon Collections library.

## Overview

Collection provides the foundation for Sequence, Dictionary, and Set by implementing common functionality and defining the contract that all concrete collections must follow. It implements the `Countable`, `IteratorAggregate`, and `Stringable` interfaces, ensuring all collections support counting, iteration, and string conversion.

While you cannot instantiate Collection directly, understanding its API helps you know what methods are available on all collection types and what behavior they share.

## Implemented Interfaces

- **Countable** - All collections support `count()`
- **IteratorAggregate** - All collections support `foreach` loops
- **Stringable** - All collections can be converted to strings with `__toString()`

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

## Constructor

### __construct()

```php
public function __construct(null|string|iterable $types = null)
```

Create a new Collection with optional type constraints.

**Parameters:**
- `$types` - Type constraints (null for any type, string for single/union types, iterable for array of types)

**Throws:**
- `TypeError` - If a type is not specified as a string
- `ValueError` - If a type name is invalid

**Note:** This is called by concrete collection constructors. You cannot instantiate Collection directly.

**Example (from Sequence):**
```php
// Internally calls parent::__construct('int')
$seq = new Sequence('int');
```

## Concrete Methods

These methods are fully implemented in Collection and available on all collection types.

### clear()

```php
public function clear(): static
```

Remove all items from the Collection. Returns `$this` for chaining.

**Examples:**
```php
$seq = new Sequence('int', source: [1, 2, 3, 4, 5]);
echo $seq->count(); // 5

$seq->clear();
echo $seq->count(); // 0

// Chaining
$seq->clear()->append(10, 20, 30);
echo $seq->count(); // 3
```

### empty()

```php
public function empty(): bool
```

Check if the Collection is empty (contains no items).

**Examples:**
```php
$seq = new Sequence('int');
var_dump($seq->empty()); // true

$seq->append(1);
var_dump($seq->empty()); // false

$seq->clear();
var_dump($seq->empty()); // true
```

### all()

```php
public function all(callable $fn): bool
```

Check if all items in the Collection pass a test function. Returns `true` if all items pass, `false` otherwise.

Analogous to PHP's `array_all()`.

**Callback signature:** `fn(mixed $item): bool`

**Important for Dictionary:** The callback receives `KeyValuePair` objects, not individual keys/values.

**Examples:**
```php
// Sequence: check if all numbers are positive
$seq = new Sequence('int', source: [1, 2, 3, 4, 5]);
var_dump($seq->all(fn($n) => $n > 0)); // true
var_dump($seq->all(fn($n) => $n > 3)); // false

// Set: check if all strings are uppercase
$set = new Set('string', ['HELLO', 'WORLD']);
var_dump($set->all(fn($s) => $s === strtoupper($s))); // true

// Dictionary: callback receives KeyValuePair objects
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

**Important for Dictionary:** The callback receives `KeyValuePair` objects, not individual keys/values.

**Examples:**
```php
// Sequence: check if any number is even
$seq = new Sequence('int', source: [1, 3, 5, 6, 7]);
var_dump($seq->any(fn($n) => $n % 2 === 0)); // true (6 is even)

// Set: check if any string starts with 'A'
$set = new Set('string', ['apple', 'banana', 'cherry']);
var_dump($set->any(fn($s) => str_starts_with($s, 'A'))); // false
var_dump($set->any(fn($s) => str_starts_with($s, 'a'))); // true

// Dictionary: callback receives KeyValuePair objects
$dict = new Dictionary('string', 'int', ['a' => 1, 'b' => 10]);
var_dump($dict->any(fn($pair) => $pair->value > 5)); // true
```

### count()

```php
public function count(): int
```

Get the number of items in the Collection. Implements the `Countable` interface.

**Examples:**
```php
$seq = new Sequence('int', source: [1, 2, 3]);
echo $seq->count(); // 3

// Also works with count() function
echo count($seq); // 3

// Empty collection
$empty = new Set();
echo $empty->count(); // 0
```

### toArray()

```php
public function toArray(): array
```

Convert the Collection to a plain PHP array. Returns the internal `$items` array.

**Note:** For Dictionary, this returns an array of `KeyValuePair` objects, not a regular associative array.

**Examples:**
```php
// Sequence
$seq = new Sequence('int', source: [1, 2, 3]);
$array = $seq->toArray();
var_dump($array); // [1, 2, 3]

// Set
$set = new Set('string', ['a', 'b', 'c']);
$array = $set->toArray();
// Array with string keys (internal representation)

// Dictionary
$dict = new Dictionary(source: ['a' => 1, 'b' => 2]);
$array = $dict->toArray();
// Array of KeyValuePair objects (not ['a' => 1, 'b' => 2])
```

### __toString()

```php
public function __toString(): string
```

Convert the Collection to a string representation. Implements the `Stringable` interface.

**Examples:**
```php
$seq = new Sequence('int', source: [1, 2, 3]);
echo $seq; // [1, 2, 3]

$set = new Set('string', ['a', 'b', 'c']);
echo $set; // {a, b, c}

$dict = new Dictionary(source: ['a' => 1]);
echo $dict; // String representation of Dictionary
```

## Abstract Methods

These methods must be implemented by concrete collection classes (Sequence, Dictionary, Set).

### import()

```php
abstract public function import(iterable $src): static
```

Import values from an iterable into the Collection. Returns `$this` for chaining.

**Must throw:** `TypeError` if any values have disallowed types.

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

### equals()

```php
abstract public function equals(Collection $other): bool
```

Check if two Collections are equal. The definition of "equal" varies by collection type.

**Implementations:**
- **Sequence:** Same type, count, values, and order
- **Dictionary:** Same type, count, keys, and values
- **Set:** Same type, count, and values (order doesn't matter)

### filter()

```php
abstract public function filter(callable $callback): static
```

Filter the Collection using a callback function. Returns a new Collection with only items where the callback returns `true`.

**Must throw:** `TypeError` if callback parameter types don't match collection types.

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

## Protected Helper Methods

### equalTypeAndCount()

```php
protected function equalTypeAndCount(Collection $other): bool
```

Check if two Collections have the same concrete type and same number of items.

**Used by:** `equals()` implementations in concrete classes as a first check before comparing values.

**Example (internal use):**
```php
// In Sequence::equals()
public function equals(Collection $other): bool
{
    // First check type and count match
    if (!$this->equalTypeAndCount($other)) {
        return false;
    }

    // Then check values...
}
```

## Usage in Concrete Collections

### Sequence Example
```php
use Galaxon\Collections\Sequence;

$seq = new Sequence('int', source: [1, 2, 3, 4, 5]);

// Collection methods
echo $seq->count();              // 5
var_dump($seq->empty());         // false
var_dump($seq->contains(3));     // true
var_dump($seq->all(fn($n) => $n > 0));  // true
var_dump($seq->any(fn($n) => $n > 4));  // true

// Sequence-specific methods
$seq->append(6, 7, 8);
$filtered = $seq->filter(fn($n) => $n % 2 === 0);
```

### Dictionary Example
```php
use Galaxon\Collections\Dictionary;

$dict = new Dictionary('string', 'int', ['a' => 1, 'b' => 2]);

// Collection methods
echo $dict->count();             // 2
var_dump($dict->empty());        // false
var_dump($dict->contains(1));    // true (checks values)
var_dump($dict->all(fn($pair) => $pair->value > 0)); // true

// Dictionary-specific methods
$dict->add('c', 3);
var_dump($dict->keyExists('a')); // true
```

### Set Example
```php
use Galaxon\Collections\Set;

$set = new Set('string', ['a', 'b', 'c']);

// Collection methods
echo $set->count();              // 3
var_dump($set->empty());         // false
var_dump($set->contains('b'));   // true
var_dump($set->all(fn($s) => strlen($s) === 1)); // true

// Set-specific methods
$set->add('d', 'e');
$other = new Set('string', ['c', 'd', 'e']);
$union = $set->union($other);
```

## Type Safety

All collections inherit type safety from Collection through the `valueTypes` property:

```php
// Type constraints enforced
$seq = new Sequence('int');
$seq->append(1, 2, 3);    // ✅ Works
$seq->append('four');      // ❌ TypeError

// Check type constraints
var_dump($seq->valueTypes->contains('int'));    // true
var_dump($seq->valueTypes->contains('string')); // false

// Any type allowed
$mixed = new Sequence();
$mixed->append(1, 'two', 3.14, true, null, []); // ✅ All work
```

## Iteration Support

All collections support `foreach` through the `IteratorAggregate` interface:

```php
$seq = new Sequence('int', source: [1, 2, 3]);

foreach ($seq as $value) {
    echo $value . "\n";
}

// With keys
foreach ($seq as $key => $value) {
    echo "$key: $value\n";
}
```

## Extending Collection

If you want to create your own collection type, extend the Collection class and implement the abstract methods:

```php
use Galaxon\Collections\Collection;

class MyCustomCollection extends Collection
{
    public function import(iterable $src): static
    {
        // Implementation
        return $this;
    }

    public function contains(mixed $value): bool
    {
        // Implementation
        return false;
    }

    public function equals(Collection $other): bool
    {
        // Implementation
        return false;
    }

    public function filter(callable $callback): static
    {
        // Implementation
        return new self();
    }

    public function getIterator(): Traversable
    {
        // Implementation
        return new ArrayIterator($this->items);
    }
}
```

## Benefits of the Shared Base Class

1. **Consistent API** - All collections have `count()`, `empty()`, `clear()`, `all()`, `any()`
2. **Type Safety** - All collections use TypeSet for runtime validation
3. **Standard Interfaces** - All collections are Countable, Iterable, and Stringable
4. **Code Reuse** - Common functionality implemented once
5. **Polymorphism** - Collections can be used interchangeably where only Collection methods are needed

## See Also

- **[Sequence](Sequence.md)** - Ordered list implementation
- **[Dictionary](Dictionary.md)** - Key-value pair implementation
- **[Set](Set.md)** - Unique values implementation
- **[TypeSet](TypeSet.md)** - Type constraint management
