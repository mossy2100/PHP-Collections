# Set

A type-safe collection of unique values with optional type constraints.

## Features

- **Uniqueness enforced**: Duplicate values are automatically prevented
- **Type constraints**: Optional runtime validation for values
- **Set operations**: union, intersection, difference
- **Subset/superset testing**: Complete set comparison methods
- **Any value type**: Objects, arrays, resources, scalars, null - everything works
- **Type inference**: Automatically detect types from source data
- **Iteration**: Full foreach support

## What is a Set?

A Set is a collection that contains only unique values. Unlike Sequence (which allows duplicates and has ordered indexes) or Dictionary (which maps keys to values), a Set simply stores unique values:

```php
$set = new Set('int');
$set->add(1, 2, 3, 2, 1); // Only 1, 2, 3 are stored
echo $set->count(); // 3
```

Sets are ideal for:
- Removing duplicates from data
- Testing membership (`contains()`)
- Mathematical set operations (union, intersection, difference)
- Tracking unique items

## Constructor

### __construct()

```php
public function __construct(
    null|string|iterable|true $types = true,
    iterable $source = []
)
```

Create a new Set with optional type constraints and initial values from a source iterable.

**Type Constraints:**

The `$types` parameter accepts:
- `null` - Values of any type are allowed
- `string` - A type name, or multiple types using union type or nullable type syntax (e.g., `'string'`, `'int|null'`, `'?int'`)
- `iterable` - Array or other collection of type names (e.g., `['string', 'int']`)
- `true` (default) - Types will be inferred automatically from the source iterable's values

**Examples:**
```php
// Empty set, no type constraints
$set = new Set();

// String values only
$set = new Set('string');

// Union type constraint
$set = new Set('int|string');

// Array of types (equivalent to 'int|string')
$set = new Set(['int', 'string']);

// Nullable type
$set = new Set('?int'); // Allows int or null

// Create from array with type inference (default)
$set = new Set(source: [1, 2, 3, 4, 5]);
echo $set->count(); // 5
// Types inferred as 'int'

// Create from array with explicit types
$set = new Set('int', [1, 2, 3]);
echo $set->count(); // 3

// Duplicates removed automatically
$set = new Set(source: [1, 2, 2, 3, 3, 3]);
echo $set->count(); // 3 (only 1, 2, 3)

// Create from generator with type inference
$generator = function() {
    yield 'apple';
    yield 'banana';
    yield 'cherry';
};
$set = new Set(source: $generator());
echo $set->count(); // 3

// Mixed types with inference
$set = new Set(source: [1, 'hello', 3.14, true]);
// Types inferred as 'int|string|float|bool'

// Object types
$set = new Set('DateTime');
$set->add(new DateTime('2024-01-01'));
$set->add(new DateTime('2024-01-02'));
```

## Adding and Removing Items

### add()

```php
public function add(mixed ...$items): self
```

Add one or more items to the Set. Duplicates are automatically ignored. Returns `$this` for chaining. Throws `TypeError` for invalid item types.

**Examples:**
```php
$set = new Set('int');
$set->add(1);
$set->add(2, 3, 4);
$set->add(...[5, 6, 7]);

echo $set->count(); // 7

// Duplicates are ignored
$set->add(1, 2, 3); // Already in set
echo $set->count(); // Still 7

// Chaining
$set->add(8)->add(9)->add(10);
```

### import()

```php
public function import(iterable $src): static
```

Import values from an iterable into the Set. Duplicates are automatically ignored. Returns `$this` for chaining. Throws `TypeError` for invalid value types.

**Example:**
```php
$set = new Set('int');
$set->add(1, 2);
$set->import([3, 4, 5, 2, 1]); // Only 3, 4, 5 added

echo $set->count(); // 5
```

### remove()

```php
public function remove(mixed $item): bool
```

Remove an item from the Set if present. Returns `true` if an item was removed, `false` if the item wasn't in the Set.

**Examples:**
```php
$set = new Set('string');
$set->add('apple', 'banana', 'cherry');

$removed = $set->remove('banana');
echo $removed; // true
echo $set->count(); // 2

$removed = $set->remove('grape');
echo $removed; // false (wasn't in set)
echo $set->count(); // Still 2
```

## Set Operations

### union()

```php
public function union(self $other): self
```

Return the union of this set and another set (all items from both sets). Returns a new Set containing items from both sets. The resulting set will allow the types allowed by both sets.

**Examples:**
```php
$set1 = new Set('int', [1, 2, 3]);
$set2 = new Set('int', [3, 4, 5]);

$union = $set1->union($set2);
// Result: {1, 2, 3, 4, 5}

// Original sets unchanged
echo $set1->count(); // 3
echo $set2->count(); // 3
echo $union->count(); // 5
```

### intersect()

```php
public function intersect(self $other): self
```

Return the intersection of this set and another set (only items present in both sets). Returns a new Set. The resulting set will allow the same types as the calling set.

**Examples:**
```php
$set1 = new Set('int', [1, 2, 3, 4]);
$set2 = new Set('int', [3, 4, 5, 6]);

$intersection = $set1->intersect($set2);
// Result: {3, 4}

echo $intersection->count(); // 2
```

### diff()

```php
public function diff(self $other): self
```

Return the difference of this set and another set (items in this set that are not in the other set). Returns a new Set. The resulting set will allow the same types as the calling set.

**Examples:**
```php
$set1 = new Set('int', [1, 2, 3, 4]);
$set2 = new Set('int', [3, 4, 5, 6]);

$difference = $set1->diff($set2);
// Result: {1, 2} (items in set1 but not in set2)

echo $difference->count(); // 2
```

## Comparison and Inspection Methods

### contains()

```php
public function contains(mixed $value): bool
```

Check if the Set contains a value. Uses strict equality (value and type must match).

**Examples:**
```php
$set = new Set('int', [1, 2, 3]);

var_dump($set->contains(2));   // true
var_dump($set->contains('2')); // false (different type)
var_dump($set->contains(4));   // false (not in set)
```

### equals()

```php
public function equals(Collection $other): bool
```

Check if two Sets are equal. Sets are equal if they have the same type (both Sets), same number of items, and same item values. Order doesn't matter. Type constraints are not considered.

**Examples:**
```php
$set1 = new Set('int', [1, 2, 3]);
$set2 = new Set('int', [3, 2, 1]); // Different order

var_dump($set1->equals($set2)); // true (order doesn't matter)

$set3 = new Set('int', [1, 2]);
var_dump($set1->equals($set3)); // false (different count)

$set4 = new Set('int', [1, 2, 4]);
var_dump($set1->equals($set4)); // false (different values)
```

### isSubsetOf()

```php
public function isSubsetOf(self $other): bool
```

Check if this set is a subset of another set (all items in this set are also in the other set).

**Examples:**
```php
$set1 = new Set('int', [1, 2]);
$set2 = new Set('int', [1, 2, 3, 4]);

var_dump($set1->isSubsetOf($set2)); // true
var_dump($set2->isSubsetOf($set1)); // false

// A set is a subset of itself
var_dump($set1->isSubsetOf($set1)); // true
```

### isProperSubsetOf()

```php
public function isProperSubsetOf(self $other): bool
```

Check if this set is a proper subset of another set (subset but not equal). This means all items in this set are in the other set, AND the other set has at least one additional item.

**Examples:**
```php
$set1 = new Set('int', [1, 2]);
$set2 = new Set('int', [1, 2, 3]);
$set3 = new Set('int', [1, 2]);

var_dump($set1->isProperSubsetOf($set2)); // true
var_dump($set1->isProperSubsetOf($set3)); // false (equal sets)
```

### isSupersetOf()

```php
public function isSupersetOf(self $other): bool
```

Check if this set is a superset of another set (contains all items from the other set).

**Examples:**
```php
$set1 = new Set('int', [1, 2, 3, 4]);
$set2 = new Set('int', [1, 2]);

var_dump($set1->isSupersetOf($set2)); // true
var_dump($set2->isSupersetOf($set1)); // false
```

### isProperSupersetOf()

```php
public function isProperSupersetOf(self $other): bool
```

Check if this set is a proper superset of another set (superset but not equal).

**Examples:**
```php
$set1 = new Set('int', [1, 2, 3]);
$set2 = new Set('int', [1, 2]);
$set3 = new Set('int', [1, 2, 3]);

var_dump($set1->isProperSupersetOf($set2)); // true
var_dump($set1->isProperSupersetOf($set3)); // false (equal sets)
```

### isDisjointFrom()

```php
public function isDisjointFrom(self $other): bool
```

Check if this set is disjoint from another set (they have no elements in common).

**Examples:**
```php
$set1 = new Set('int', [1, 2, 3]);
$set2 = new Set('int', [4, 5, 6]);
$set3 = new Set('int', [3, 4, 5]);

var_dump($set1->isDisjointFrom($set2)); // true (no overlap)
var_dump($set1->isDisjointFrom($set3)); // false (3 is in both)
```

## Collection Methods

### count()

```php
public function count(): int
```

Returns the number of items in the Set.

**Example:**
```php
$set = new Set('int', [1, 2, 3]);
echo $set->count(); // 3
```

### empty()

```php
public function empty(): bool
```

Check if the Set is empty.

**Example:**
```php
$set = new Set();
var_dump($set->empty()); // true

$set->add(1);
var_dump($set->empty()); // false
```

### clear()

```php
public function clear(): void
```

Remove all items from the Set.

**Example:**
```php
$set = new Set('int', [1, 2, 3]);
echo $set->count(); // 3

$set->clear();
echo $set->count(); // 0
```

### filter()

```php
public function filter(callable $callback): static
```

Filter the Set using a callback function. Returns a new Set with the same type constraints, containing only values where the callback returns `true`. Throws `TypeError` if the callback doesn't return a bool.

**Examples:**
```php
$set = new Set('int', [1, 2, 3, 4, 5, 6]);

// Keep even numbers only
$evens = $set->filter(fn($n) => $n % 2 === 0);
// Result: {2, 4, 6}

// Keep numbers greater than 3
$large = $set->filter(fn($n) => $n > 3);
// Result: {4, 5, 6}

// Original unchanged
echo $set->count(); // 6
```

## Conversion Methods

### toDictionary()

```php
public function toDictionary(): Dictionary
```

Convert the Set to a Dictionary. The keys will be sequential unsigned integers starting from 0, and the values will be the Set items.

**Example:**
```php
$set = new Set('string', ['apple', 'banana', 'cherry']);
$dict = $set->toDictionary();

// Result: Dictionary with:
// 0 => 'apple'
// 1 => 'banana'
// 2 => 'cherry'

echo $dict[0]; // 'apple'
echo $dict->count(); // 3
```

### toSequence()

```php
public function toSequence(): Sequence
```

Convert the Set to a Sequence. The values will maintain the same type constraints.

**Example:**
```php
$set = new Set('int', [3, 1, 2]);
$seq = $set->toSequence();

// Result: Sequence with items in set order
echo $seq->count(); // 3
echo $seq[0]; // First item from set
```

### toArray()

```php
public function toArray(): array
```

Convert the Set to a plain PHP array.

**Example:**
```php
$set = new Set('string', ['apple', 'banana', 'cherry']);
$array = $set->toArray();

var_dump($array); // ['apple', 'banana', 'cherry']
```

### __toString()

```php
public function __toString(): string
```

Get a string representation of the Set using set notation `{}`.

**Example:**
```php
$set = new Set('int', [1, 2, 3]);
echo $set; // {1, 2, 3}

$set = new Set('string', ['apple', 'banana']);
echo $set; // {'apple', 'banana'}

$empty = new Set();
echo $empty; // {}
```

## Iteration

Sets support `foreach` iteration:

```php
$set = new Set('string', ['apple', 'banana', 'cherry']);

foreach ($set as $fruit) {
    echo $fruit . "\n";
}
// Output:
// apple
// banana
// cherry

// With keys (auto-generated sequential integers)
foreach ($set as $key => $fruit) {
    echo "$key: $fruit\n";
}
// Output:
// 0: apple
// 1: banana
// 2: cherry
```

## Practical Examples

### Remove duplicates from an array
```php
$array = [1, 2, 2, 3, 3, 3, 4, 4, 4, 4];
$set = new Set(source: $array);
$unique = $set->toArray();

var_dump($unique); // [1, 2, 3, 4]
```

### Find common elements between arrays
```php
$array1 = [1, 2, 3, 4, 5];
$array2 = [4, 5, 6, 7, 8];

$set1 = new Set(source: $array1);
$set2 = new Set(source: $array2);

$common = $set1->intersect($set2);
echo $common; // {4, 5}
```

### Find unique elements in first array
```php
$array1 = [1, 2, 3, 4, 5];
$array2 = [4, 5, 6, 7, 8];

$set1 = new Set(source: $array1);
$set2 = new Set(source: $array2);

$unique = $set1->diff($set2);
echo $unique; // {1, 2, 3}
```

### Check if all items from one array exist in another
```php
$required = ['username', 'email'];
$provided = ['username', 'email', 'password', 'age'];

$requiredSet = new Set(source: $required);
$providedSet = new Set(source: $provided);

if ($requiredSet->isSubsetOf($providedSet)) {
    echo "All required fields provided";
}
```

### Track unique visitors
```php
$visitors = new Set('string');

$visitors->add('alice@example.com');
$visitors->add('bob@example.com');
$visitors->add('alice@example.com'); // Duplicate ignored

echo $visitors->count(); // 2 (unique visitors)
```

## Type Safety Examples

```php
// Strict type checking
$set = new Set('int');
$set->add(1, 2, 3);
// $set->add('4'); // TypeError: Expected int, got string

// Union types
$set = new Set('int|string');
$set->add(1, 'two', 3, 'four'); // ✅ Works

// Nullable types
$set = new Set('?int');
$set->add(1, 2, null, 3); // ✅ Works

// Object types
$set = new Set('DateTime');
$set->add(new DateTime('2024-01-01'));
$set->add(new DateTime('2024-01-02'));
// $set->add('2024-01-03'); // TypeError: Expected DateTime, got string

// No constraints
$set = new Set();
$set->add(1, 'two', 3.14, true, null, []); // ✅ All types allowed
```
