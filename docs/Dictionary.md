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
    string|iterable|null $key_types = null,
    string|iterable|null $value_types = null
)
```

Create a Dictionary with optional type constraints.

**Type Specification:**
- **null** - Any type allowed (no constraints)
- **String** - e.g. `'int'` allows only ints
- **String with union type syntax** - e.g. `'int|string'` allows int OR string
- **String with nullable syntax** - e.g. `'?int'` allows int OR null
- **Array (or other collection) of strings** - e.g. `['int', 'string']` allows int OR string

**Note:** String union syntax and array syntax cannot be combined. Use `'int|string'` OR `['int', 'string']`, not `['int|string', 'float']`.

**Examples:**
```php
// No type constraints
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
```

## Factory Methods

### fromIterable()

```php
public static function fromIterable(
    iterable $src,
    string|iterable|null|true $key_types = true,
    string|iterable|null|true $value_types = true
): static
```

Create Dictionary from an iterable with automatic type inference.

**Type Specification:**
- `true` (default) - Automatically infer types from the data
- `string`, `iterable`, or `null` - Work the same as in the constructor (see above)

**Examples:**
```php
// Infer both key and value types (default)
$dict = Dictionary::fromIterable(['a' => 1, 'b' => 2]);
// Result: keyTypes = 'string', valueTypes = 'int'

// Mixed types - infers union types
$dict = Dictionary::fromIterable([1 => 'one', 'two' => 2]);
// Result: keyTypes = 'int|string', valueTypes = 'string|int'

// Explicit type constraints (no inference)
$dict = Dictionary::fromIterable(['a' => 1], 'string', 'int');

// Explicit union types
$dict = Dictionary::fromIterable($data, 'int|string', ['float', 'bool']);

// No type constraints (any type allowed)
$dict = Dictionary::fromIterable(['a' => 1], null, null);

// From another Dictionary
$copy = Dictionary::fromIterable($original);
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
public function removeByKey(mixed $key): self
```

Remove item by key. Returns `$this` for chaining.

**Example:**
```php
$dict->removeByKey('obsolete');
```

### removeByValue()

```php
public function removeByValue(mixed $value): self
```

Remove all items with matching value. Returns `$this` for chaining.

**Example:**
```php
$dict->removeByValue(null); // Remove all null values
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
$dict = Dictionary::fromIterable(['a' => 1, 'b' => 2]);
$keys = $dict->keys(); // ['a', 'b']
```

### values()

```php
public function values(): array
```

Get all values as an array.

**Example:**
```php
$dict = Dictionary::fromIterable(['a' => 1, 'b' => 2]);
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
$dict1 = Dictionary::fromIterable(['a' => 1, 'b' => 2]);
$dict2 = Dictionary::fromIterable(['a' => 1, 'b' => 2]);

var_dump($dict1->eq($dict2)); // true
```

### all()

```php
public function all(callable $fn): bool
```

Check if all items pass a test.

**Example:**
```php
$dict = Dictionary::fromIterable(['a' => 1, 'b' => 2]);

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
$dict = Dictionary::fromIterable(['z' => 1, 'a' => 2, 'm' => 3]);
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
$dict = Dictionary::fromIterable(['a' => 30, 'b' => 10, 'c' => 20]);
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
$dict = Dictionary::fromIterable([
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

Swap keys with values. Duplicate values overwrite earlier entries (like `array_flip()`).

**Example:**
```php
$dict = Dictionary::fromIterable(['a' => 1, 'b' => 2]);
$flipped = $dict->flip();
// Result: [1 => 'a', 2 => 'b']

// Duplicate values - last wins
$dict = Dictionary::fromIterable(['a' => 1, 'b' => 1, 'c' => 2]);
$flipped = $dict->flip();
// Result: [1 => 'b', 2 => 'c']  ('b' overwrites 'a')
```

### merge()

```php
public function merge(self $other): self
```

Return new Dictionary with items from both. Duplicate keys keep value from second Dictionary.

**Example:**
```php
$dict1 = Dictionary::fromIterable(['a' => 1, 'b' => 2]);
$dict2 = Dictionary::fromIterable(['b' => 20, 'c' => 3]);
$merged = $dict1->merge($dict2);
// Result: ['a' => 1, 'b' => 20, 'c' => 3]
```

## Iteration

Full foreach support with original keys:

```php
$dict = Dictionary::fromIterable(['name' => 'Alice', 'age' => 30]);

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

### toSet()

```php
public function toSet(): Set
```

Convert to Set of unique KeyValuePairs.

**Example:**
```php
$set = $dict->toSet();
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
$sales = Dictionary::fromIterable([
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
$users = Dictionary::fromIterable([
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
