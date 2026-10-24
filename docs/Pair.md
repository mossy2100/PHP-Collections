# Pair

A simple, immutable class for encapsulating a key-value pair where both the key and value can be any type.

## Overview

Pair is a readonly class used internally by Dictionary to store key-value pairs in its internal array. Since PHP arrays
only accept string or integer keys, Dictionary uses Pair objects to enable keys of any type (objects, arrays, resources,
etc.).

While primarily an internal implementation detail, Pair can be useful in your own code when you need to represent a
key-value association with unrestricted types.

### Key Features

- **Any type for key**: Objects, arrays, resources, scalars, null - everything works
- **Any type for value**: No restrictions on value types
- **Readonly/Immutable**: Once created, key and value cannot be changed
- **Simple and lightweight**: Just two public properties, no methods
- **Type-safe storage**: Maintains exact types (no coercion)

## Properties

### key

```php
public mixed $key
```

The key of the pair. Can be any type. The class is `readonly`, so this property cannot be changed after construction.

**Example:**
```php
$pair = new Pair('name', 'Alice');
echo $pair->key; // 'name'

// Cannot modify (class is readonly)
// $pair->key = 'other'; // Error: Cannot modify readonly property
```

### value

```php
public mixed $value
```

The value of the pair. Can be any type. The class is `readonly`, so this property cannot be changed after construction.

**Example:**
```php
$pair = new Pair('name', 'Alice');
echo $pair->value; // 'Alice'

// Cannot modify (class is readonly)
// $pair->value = 'Bob'; // Error: Cannot modify readonly property
```

## Constructor

### \_\_construct()

```php
public function __construct(public mixed $key, public mixed $value)
```

Create a Pair with the given key and value using constructor promotion.

**Parameters:**
- `$key` - The key (any type)
- `$value` - The value (any type)

**Examples:**
```php
// String key, string value
$pair = new Pair('name', 'Alice');
echo $pair->key;   // 'name'
echo $pair->value; // 'Alice'

// Integer key
$pair = new Pair(42, 'answer');
echo $pair->key;   // 42
echo $pair->value; // 'answer'

// Object key
$date = new DateTime('2024-01-01');
$pair = new Pair($date, 'New Year');
var_dump($pair->key instanceof DateTime); // true

// Array key
$coords = [10, 20];
$pair = new Pair($coords, 'position');
var_dump($pair->key); // [10, 20]

// Boolean key
$pair = new Pair(true, 'yes');
var_dump($pair->key);   // true
var_dump($pair->value); // 'yes'

// Null key or value
$pair = new Pair(null, 'empty');
$pair = new Pair('missing', null);

// Resource key
$handle = fopen('php://memory', 'r');
$pair = new Pair($handle, 'stream data');
var_dump(is_resource($pair->key)); // true
fclose($handle);
```

## Usage Examples

### Usage in Dictionary

Dictionary uses Pair internally to store all key-value associations:

```php
$dict = new Dictionary();

// Internally, Dictionary creates Pair objects
$dict[new DateTime('2024-01-01')] = 'event';
$dict[[1, 2, 3]] = 'coordinates';
$dict[true] = 'yes';

// Each entry is stored as a Pair in the internal array
```

### Immutability

Pair is a readonly class, meaning once created, its properties cannot be modified:

```php
$pair = new Pair('key', 'value');

// These work (reading)
echo $pair->key;   // 'key'
echo $pair->value; // 'value'

// These fail (writing)
// $pair->key = 'new key';     // Error: Cannot modify readonly property
// $pair->value = 'new value'; // Error: Cannot modify readonly property

// To "change" a pair, create a new one
$newPair = new Pair('new key', 'new value');
```

This immutability ensures that Pair objects are safe to use in collections without worrying about external
modifications.

### Type Preservation

Pair preserves the exact types of both key and value:

```php
// Integer vs string
$pair1 = new Pair(42, 'int key');
$pair2 = new Pair('42', 'string key');

var_dump($pair1->key === 42);    // true
var_dump($pair2->key === '42');  // true
var_dump($pair1->key === $pair2->key); // false (different types)

// Objects maintain identity
$date1 = new DateTime('2024-01-01');
$date2 = new DateTime('2024-01-01');

$pair1 = new Pair($date1, 'first');
$pair2 = new Pair($date2, 'second');

var_dump($pair1->key === $date1); // true (same instance)
var_dump($pair1->key === $date2); // false (different instances)
```

## See Also

- **[Dictionary](Dictionary.md)** - Key-value collection that uses Pair internally
- **[Collection](Collection.md)** - Abstract base class for collections
