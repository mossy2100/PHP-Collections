# Dictionary

A type-safe key-value collection that accepts any PHP type for both keys and values.

## Features

- **Any type for keys**: Objects, arrays, resources, scalars, null - everything works
- **Type constraints**: Optional runtime validation for both keys and values
- **ArrayAccess**: Use familiar `$dict[$key]` syntax
- **Iteration**: Full foreach support
- **Type inference**: Automatically detect types from data
- **Transformation methods**: filter, flip, merge, sort

## Why Dictionary?

PHP arrays only accept `string` or `int` keys. Dictionary lets you use:

```php
$dict = new Dictionary();
$dict[new DateTime()] = 'event';        // ✅ Object keys
$dict[[1, 2, 3]] = 'coordinates';       // ✅ Array keys
$dict[fopen('file.txt', 'r')] = 'data'; // ✅ Resource keys
$dict[true] = 'yes';                    // ✅ Boolean keys
$dict[null] = 'empty';                  // ✅ Null key
```

## Constructor

### __construct()

```php
public function __construct(
    null|string|iterable|true $key_types = true,
    null|string|iterable|true $value_types = true,
    iterable $source = []
)
```

Create a Dictionary with optional type constraints and initial key-value pairs from a source iterable.

**Type Constraints:**

The `$key_types` and `$value_types` parameters accept:
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

// Nullable values (using ?)
$dict = new Dictionary('string', '?int');

// Nullable values (using array)
$dict = new Dictionary('string', ['int', 'null']);

// Create from array with type inference (default)
$dict = new Dictionary(source: ['a' => 1, 'b' => 2, 'c' => 3]);
echo $dict->count(); // 3
// Types inferred: keyTypes = 'string', valueTypes = 'int'

// Create from array with explicit types
$dict = new Dictionary('string', 'int', ['a' => 1, 'b' => 2]);
echo $dict->count(); // 2

// Mixed types - infers union types
$dict = new Dictionary(source: [1 => 'one', 'two' => 2, 3 => true]);
// Types inferred: keyTypes = 'int|string', valueTypes = 'string|int|bool'

// No type constraints (any type allowed)
$dict = new Dictionary(null, null, ['a' => 1, 'b' => 'text']);

// From another Dictionary
$original = new Dictionary('string', 'int');
$original->add('x', 10);
$copy = new Dictionary(source: $original);
echo $copy->count(); // 1

// From generator with type inference
$generator = function() {
    yield 'a' => 10;
    yield 'b' => 20;
    yield 'c' => 30;
};
$dict = new Dictionary(source: $generator());
echo $dict->count(); // 3
```

## Factory Methods

### combine()

```php
public static function combine(
    iterable $keys,
    iterable $values,
    bool $infer_types = true
): self
```

Create a new Dictionary by combining separate iterables of keys and values.

**Parameters:**
- `$keys` - Iterable of keys
- `$values` - Iterable of values
- `$infer_types` - Whether to infer key and value types from the data (default: `true`)

**Type Inference:**
- When `$infer_types = true` (default), types are automatically inferred from the provided keys and values
- When `$infer_types = false`, no type constraints are applied (any type allowed)

**Throws:**
- `ValueError` if the iterables have different counts
- `ValueError` if keys are not unique

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
$keys = [$date1, $date2];
$values = ['New Year', 'February'];
$dict = Dictionary::combine($keys, $values);
echo $dict[$date1]; // 'New Year'

// With array keys
$coords1 = [10, 20];
$coords2 = [30, 40];
$keys = [$coords1, $coords2];
$values = ['Location A', 'Location B'];
$dict = Dictionary::combine($keys, $values);
echo $dict[[10, 20]]; // 'Location A'

// Disable type inference for maximum flexibility
$keys = ['a', 'b'];
$values = [1, 2];
$dict = Dictionary::combine($keys, $values, false);
// No type constraints - can add any types later
$dict->add(123, 'text');      // ✅ Works
$dict->add(true, [1, 2, 3]);  // ✅ Works

// With generators
$keysGen = function() {
    yield 'key1';
    yield 'key2';
};
$valuesGen = function() {
    yield 100;
    yield 200;
};
$dict = Dictionary::combine($keysGen(), $valuesGen());

// Error: Mismatched counts
$keys = ['a', 'b', 'c'];
$values = [1, 2];
$dict = Dictionary::combine($keys, $values);
// ValueError: Cannot combine: keys count (3) does not match values count (2).

// Error: Duplicate keys
$keys = ['a', 'b', 'a'];
$values = [1, 2, 3];
$dict = Dictionary::combine($keys, $values);
// ValueError: Cannot combine: keys are not unique.
```

## Adding and Removing Items

### add()

```php
public function add(mixed $key, mixed $value): self
// OR
public function add(KeyValuePair $pair): self
```

Add a key-value pair. Returns `$this` for chaining.

**Examples:**
```php
$dict = new Dictionary('string', 'int');
$dict->add('count', 42);
$dict->add('total', 100);

// Using KeyValuePair
$dict->add(new KeyValuePair('average', 71));

// Chaining
$dict->add('min', 10)->add('max', 90);
```

### import()

```php
public function import(iterable $src): static
```

Import multiple key-value pairs. Returns `$this` for chaining.

**Example:**
```php
$dict = new Dictionary('string', 'int');
$dict->import(['a' => 1, 'b' => 2, 'c' => 3]);
```

### removeByKey()

```php
public function removeByKey(mixed $key): mixed
```

Remove item by key. Returns the value of the removed item.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2, 'c' => 3]);
$value = $dict->removeByKey('b');
echo $value; // 2
echo $dict->count(); // 2 (item removed)
```

### removeByValue()

```php
public function removeByValue(mixed $value): int
```

Remove all items with matching value. Returns the count of items removed.

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

Remove all items.

**Example:**
```php
$dict->clear();
```

## Accessing Items

### ArrayAccess

Use familiar array syntax:

```php
$dict = new Dictionary('string', 'int');

// Set
$dict['count'] = 42;

// Get
echo $dict['count']; // 42

// Check
if (isset($dict['count'])) { ... }

// Unset
unset($dict['count']);
```

**Important:** `$dict[]` syntax uses `null` as the key (not append like arrays).

### keys()

```php
public function keys(): array
```

Get all keys as an array.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2]);
$keys = $dict->keys(); // ['a', 'b']
```

### values()

```php
public function values(): array
```

Get all values as an array.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2]);
$values = $dict->values(); // [1, 2]
```

## Inspection Methods

### contains()

```php
public function contains(mixed $value): bool
```

Check if Dictionary contains a value (strict equality).

**Example:**
```php
if ($dict->contains(42)) {
    echo "Found 42";
}
```

### keyExists()

```php
public function keyExists(mixed $key): bool
```

Check if a key exists.

**Example:**
```php
if ($dict->keyExists('username')) {
    echo $dict['username'];
}
```

### empty()

```php
public function empty(): bool
```

Check if Dictionary is empty.

**Example:**
```php
if ($dict->empty()) {
    echo "No data";
}
```

### count()

```php
public function count(): int
```

Get number of key-value pairs.

**Example:**
```php
echo count($dict);        // Countable interface
echo $dict->count();      // Direct method
```

### eq()

```php
public function eq(Collection $other): bool
```

Check equality. Dictionaries are equal if they:
- Are both Dictionary instances
- Have same number of items
- Have same keys (strict equality)
- Have same values (strict equality)
- Have same order

Type constraints are ignored.

**Example:**
```php
$dict1 = new Dictionary(source: ['a' => 1, 'b' => 2]);
$dict2 = new Dictionary(source: ['a' => 1, 'b' => 2]);

var_dump($dict1->eq($dict2)); // true
```

### all()

```php
public function all(callable $fn): bool
```

Check if all items pass a test.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2]);

// Note: Callback receives KeyValuePair objects
$allPositive = $dict->all(fn($pair) => $pair->value > 0);
```

### any()

```php
public function any(callable $fn): bool
```

Check if any item passes a test.

**Example:**
```php
$hasLargeValue = $dict->any(fn($pair) => $pair->value > 100);
```

## Sorting Methods

### sort()

```php
public function sort(callable $fn): self
```

Sort by custom comparison function (mutating). Returns `$this`.

**Example:**
```php
// Sort by value
$dict->sort(fn($a, $b) => $a->value <=> $b->value);
```

### sortByKey()

```php
public function sortByKey(): self
```

Sort by keys using spaceship operator (mutating). Returns `$this`.

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

Sort by values using spaceship operator (mutating). Returns `$this`.

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

Return new Dictionary with items that pass the test.

**Example:**
```php
$dict = new Dictionary(source: [
    'apple' => 5,
    'banana' => 3,
    'cherry' => 8
]);

// Filter by value
$expensive = $dict->filter(fn($key, $value) => $value > 4);
// Result: ['apple' => 5, 'cherry' => 8]

// Filter by key
$aFruits = $dict->filter(fn($key, $value) => str_starts_with($key, 'a'));
// Result: ['apple' => 5]
```

### flip()

```php
public function flip(): self
```

Swap keys with values. All values in the Dictionary must be unique.

**Throws:** `ValueError` if the Dictionary contains duplicate values.

**Example:**
```php
$dict = new Dictionary(source: ['a' => 1, 'b' => 2, 'c' => 3]);
$flipped = $dict->flip();
// Result: [1 => 'a', 2 => 'b', 3 => 'c']

// Duplicate values cause an error
$dict = new Dictionary(source: ['a' => 1, 'b' => 1, 'c' => 2]);
try {
    $flipped = $dict->flip();
} catch (ValueError $e) {
    echo $e->getMessage(); // "Cannot flip Dictionary: values are not unique."
}
```

### merge()

```php
public function merge(self $other): self
```

Return new Dictionary with items from both. Duplicate keys keep value from second Dictionary.

**Example:**
```php
$dict1 = new Dictionary(source: ['a' => 1, 'b' => 2]);
$dict2 = new Dictionary(source: ['b' => 20, 'c' => 3]);
$merged = $dict1->merge($dict2);
// Result: ['a' => 1, 'b' => 20, 'c' => 3]
```

## Iteration

Full foreach support with original keys:

```php
$dict = new Dictionary(source: ['name' => 'Alice', 'age' => 30]);

foreach ($dict as $key => $value) {
    echo "$key: $value\n";
}
// Output:
// name: Alice
// age: 30
```

## Conversion Methods

### toArray()

```php
public function toArray(): array
```

Convert to array of KeyValuePair objects.

**Example:**
```php
$pairs = $dict->toArray();
```

### toSequence()

```php
public function toSequence(): Sequence
```

Convert to Sequence of KeyValuePairs.

**Example:**
```php
$seq = $dict->toSequence();
```

## Usage Examples

### Type-safe configuration

```php
$config = new Dictionary('string', 'int|string|bool');
$config['debug'] = true;
$config['maxItems'] = 100;
$config['apiKey'] = 'abc123';

// This would throw TypeError:
// $config['timeout'] = 3.14; // float not allowed
```

### Object keys for caching

```php
$cache = new Dictionary('DateTime', 'string');
$timestamp = new DateTime('2025-01-01');
$cache[$timestamp] = 'New Year Data';

echo $cache[$timestamp]; // 'New Year Data'
```

### Resource tracking

```php
$files = new Dictionary('resource', 'string');
$handle = fopen('data.txt', 'r');
$files[$handle] = 'data.txt';

// Later...
if ($files->keyExists($handle)) {
    echo "File: " . $files[$handle];
}
```

### Data transformation pipeline

```php
$sales = new Dictionary(source: [
    'Product A' => 1500,
    'Product B' => 3200,
    'Product C' => 800
]);

// Filter high-value products
$highValue = $sales->filter(fn($k, $v) => $v > 1000);

// Sort by value
$highValue->sortByValue();

foreach ($highValue as $product => $amount) {
    echo "$product: $" . number_format($amount) . "\n";
}
```

### Grouping data

```php
$users = new Dictionary(source: [
    'alice@example.com' => 'Alice',
    'bob@example.com' => 'Bob',
    'charlie@test.com' => 'Charlie'
]);

// Group by domain
$byDomain = $users->filter(fn($k, $v) => str_ends_with($k, '@example.com'));
```

## Comparison with Other PHP Collections

### Dictionary vs WeakMap

**WeakMap:**
- ✅ Automatic garbage collection (weak references)
- ✅ Prevents memory leaks for object keys
- ❌ Object keys only
- ❌ No type safety for values
- ❌ No ArrayAccess support
- **Use when:** You need object keys with automatic cleanup

**Dictionary:**
- ✅ Any type for keys (objects, arrays, resources, scalars, null)
- ✅ Type safety for both keys and values
- ✅ ArrayAccess support (`$dict[$key]`)
- ✅ Rich transformation methods (filter, sort, merge)
- ❌ Strong references (manual memory management)
- **Use when:** You need flexible key types, type safety, or non-object keys

### Dictionary vs SplObjectStorage

**SplObjectStorage:**
- ✅ Object keys
- ✅ Attach arbitrary data to objects
- ❌ Object keys only
- ❌ No type safety
- ❌ Awkward API (`attach()`, `detach()` instead of array syntax)
- **Use when:** PHP 5.x compatibility needed

**Dictionary:**
- ✅ Any type for keys
- ✅ Type safety for both keys and values
- ✅ Modern, intuitive API with ArrayAccess
- ✅ Better iteration (preserves key-value semantics)
- **Use when:** Building modern PHP 8.3+ applications

### Quick Decision Guide

- **Need weak references?** → Use `WeakMap`
- **Only object keys, no other features?** → Use `WeakMap` or `SplObjectStorage`
- **Need array, resource, or scalar keys?** → Use `Dictionary`
- **Need type safety?** → Use `Dictionary`
- **Need transformation methods?** → Use `Dictionary`
- **Building new PHP 8.3+ code?** → Use `Dictionary`
