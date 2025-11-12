# KeyValuePair

A simple, immutable class for encapsulating a key-value pair where both the key and value can be any type.

## Overview

KeyValuePair is a readonly class used internally by Dictionary to store key-value pairs in its internal array. Since PHP arrays only accept string or integer keys, Dictionary uses KeyValuePair objects to enable keys of any type (objects, arrays, resources, etc.).

While primarily an internal implementation detail, KeyValuePair can be useful in your own code when you need to represent a key-value association with unrestricted types.

## Features

- **Any type for key**: Objects, arrays, resources, scalars, null - everything works
- **Any type for value**: No restrictions on value types
- **Readonly/Immutable**: Once created, key and value cannot be changed
- **Simple and lightweight**: Just two public properties, no methods
- **Type-safe storage**: Maintains exact types (no coercion)

## Constructor

### __construct()

```php
public function __construct(public mixed $key, public mixed $value)
```

Create a KeyValuePair with the given key and value.

**Properties:**
- `$key` - The key (readonly, public, any type)
- `$value` - The value (readonly, public, any type)

**Examples:**
```php
// String key, string value
$pair = new KeyValuePair('name', 'Alice');
echo $pair->key;   // 'name'
echo $pair->value; // 'Alice'

// Integer key
$pair = new KeyValuePair(42, 'answer');
echo $pair->key;   // 42
echo $pair->value; // 'answer'

// Object key
$date = new DateTime('2024-01-01');
$pair = new KeyValuePair($date, 'New Year');
var_dump($pair->key instanceof DateTime); // true

// Array key
$coords = [10, 20];
$pair = new KeyValuePair($coords, 'position');
var_dump($pair->key); // [10, 20]

// Boolean key
$pair = new KeyValuePair(true, 'yes');
var_dump($pair->key);   // true
var_dump($pair->value); // 'yes'

// Null key
$pair = new KeyValuePair(null, 'empty');
var_dump($pair->key);   // null
var_dump($pair->value); // 'empty'

// Null value
$pair = new KeyValuePair('missing', null);
var_dump($pair->key);   // 'missing'
var_dump($pair->value); // null

// Resource key
$handle = fopen('php://memory', 'r');
$pair = new KeyValuePair($handle, 'stream data');
var_dump(is_resource($pair->key)); // true
fclose($handle);
```

## Usage in Dictionary

Dictionary uses KeyValuePair internally to store all key-value associations:

```php
$dict = new Dictionary();

// Internally, Dictionary creates KeyValuePair objects
$dict[new DateTime('2024-01-01')] = 'event';
$dict[[1, 2, 3]] = 'coordinates';
$dict[true] = 'yes';

// Each entry is stored as a KeyValuePair in the internal array
```

## Properties

### key

```php
public readonly mixed $key
```

The key of the pair. Can be any type. Once set in the constructor, it cannot be changed.

**Example:**
```php
$pair = new KeyValuePair('name', 'Alice');
echo $pair->key; // 'name'

// Cannot modify (readonly)
// $pair->key = 'other'; // Error: Cannot modify readonly property
```

### value

```php
public readonly mixed $value
```

The value of the pair. Can be any type. Once set in the constructor, it cannot be changed.

**Example:**
```php
$pair = new KeyValuePair('name', 'Alice');
echo $pair->value; // 'Alice'

// Cannot modify (readonly)
// $pair->value = 'Bob'; // Error: Cannot modify readonly property
```

## Practical Examples

### Using in custom code
```php
// Store configuration entries
$configs = [];
$configs[] = new KeyValuePair('timeout', 30);
$configs[] = new KeyValuePair('retries', 3);
$configs[] = new KeyValuePair('debug', true);

foreach ($configs as $pair) {
    echo "{$pair->key} = {$pair->value}\n";
}
// Output:
// timeout = 30
// retries = 3
// debug = 1
```

### Return multiple values from function
```php
function getUserInfo(int $id): KeyValuePair
{
    // Fetch user data...
    return new KeyValuePair($id, ['name' => 'Alice', 'email' => 'alice@example.com']);
}

$result = getUserInfo(42);
echo "User ID: {$result->key}\n";
echo "Name: {$result->value['name']}\n";
```

### Associating complex keys with values
```php
// Use objects as keys
$products = [];

$product1 = new Product('SKU-001', 'Widget');
$product2 = new Product('SKU-002', 'Gadget');

$products[] = new KeyValuePair($product1, ['price' => 29.99, 'stock' => 100]);
$products[] = new KeyValuePair($product2, ['price' => 49.99, 'stock' => 50]);

foreach ($products as $pair) {
    echo "{$pair->key->name}: \${$pair->value['price']}\n";
}
```

### Storing array-to-value mappings
```php
// Map coordinate arrays to location names
$locations = [];
$locations[] = new KeyValuePair([40.7128, -74.0060], 'New York');
$locations[] = new KeyValuePair([51.5074, -0.1278], 'London');
$locations[] = new KeyValuePair([35.6762, 139.6503], 'Tokyo');

foreach ($locations as $pair) {
    [$lat, $lon] = $pair->key;
    echo "{$pair->value}: $lat, $lon\n";
}
// Output:
// New York: 40.7128, -74.006
// London: 51.5074, -0.1278
// Tokyo: 35.6762, 139.6503
```

### Preserving exact types
```php
// Unlike PHP arrays, KeyValuePair preserves exact key types
$pairs = [];
$pairs[] = new KeyValuePair(1, 'one');      // int key
$pairs[] = new KeyValuePair('1', 'uno');    // string key
$pairs[] = new KeyValuePair(true, 'yes');   // bool key
$pairs[] = new KeyValuePair(1.0, 'float');  // float key

foreach ($pairs as $pair) {
    echo get_debug_type($pair->key) . ": {$pair->key} => {$pair->value}\n";
}
// Output:
// int: 1 => one
// string: 1 => uno
// bool: 1 => yes
// float: 1 => float

// Compare to PHP arrays, which coerce keys:
$array = [];
$array[1] = 'one';      // int key
$array['1'] = 'uno';    // converted to int 1, overwrites
$array[true] = 'yes';   // converted to int 1, overwrites
$array[1.0] = 'float';  // converted to int 1, overwrites

var_dump($array); // [1 => 'float'] - only one entry!
```

## Why KeyValuePair?

### The Problem with PHP Arrays

PHP arrays have a significant limitation: keys can only be strings or integers. This means:

```php
$array = [];

// These keys get coerced
$array[true] = 'a';    // true → 1
$array[1] = 'b';       // Overwrites above
$array['1'] = 'c';     // '1' → 1, overwrites again
$array[1.5] = 'd';     // 1.5 → 1, overwrites again

var_dump(count($array)); // 1 (not 4!)

// These keys aren't allowed at all
$array[new DateTime()] = 'error';  // Fatal error
$array[[1, 2]] = 'error';          // Illegal offset type
```

### The KeyValuePair Solution

KeyValuePair allows Dictionary to accept any type as a key:

```php
$dict = new Dictionary();

// All types work, no coercion
$dict[true] = 'boolean';
$dict[1] = 'integer';
$dict['1'] = 'string';
$dict[1.5] = 'float';

echo $dict->count(); // 4 (all preserved!)

// Complex keys work too
$dict[new DateTime('2024-01-01')] = 'date';
$dict[[1, 2, 3]] = 'array';
$dict[fopen('php://memory', 'r')] = 'resource';
```

## Readonly Behavior

KeyValuePair is a readonly class, meaning once created, its properties cannot be modified:

```php
$pair = new KeyValuePair('key', 'value');

// These work (reading)
echo $pair->key;   // 'key'
echo $pair->value; // 'value'

// These fail (writing)
$pair->key = 'new key';     // Error: Cannot modify readonly property
$pair->value = 'new value'; // Error: Cannot modify readonly property

// To "change" a pair, create a new one
$newPair = new KeyValuePair('new key', 'new value');
```

This immutability ensures that KeyValuePair objects are safe to use in collections without worrying about external modifications.

## Type Preservation

KeyValuePair preserves the exact types of both key and value:

```php
// Integer vs string
$pair1 = new KeyValuePair(42, 'int key');
$pair2 = new KeyValuePair('42', 'string key');

var_dump($pair1->key === 42);    // true
var_dump($pair2->key === '42');  // true
var_dump($pair1->key === $pair2->key); // false (different types)

// Objects maintain identity
$date1 = new DateTime('2024-01-01');
$date2 = new DateTime('2024-01-01');

$pair1 = new KeyValuePair($date1, 'first');
$pair2 = new KeyValuePair($date2, 'second');

var_dump($pair1->key === $date1); // true (same instance)
var_dump($pair1->key === $date2); // false (different instances)
```

## Comparison with Standard Library

PHP's `SplObjectStorage` allows objects as keys but:
- Only works with objects (not arrays, resources, scalars)
- More complex API
- Primarily designed for object-to-data mapping

KeyValuePair (via Dictionary) allows:
- **Any type** as keys (objects, arrays, resources, scalars, null)
- Simple, familiar array-like syntax
- Type constraints for both keys and values
- Full suite of collection methods

## Performance Considerations

KeyValuePair is extremely lightweight:
- Two public properties (just memory for the key and value)
- No methods (zero method overhead)
- Readonly (no defensive copying needed)

The overhead compared to a plain PHP array entry is minimal - just the object wrapper itself.
