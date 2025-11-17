# Galaxon Collections

A type-safe collection library for PHP 8.4+ that extends PHP's array capabilities with runtime type validation, immutable operations, and support for any type as keys.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Documentation](docs/)** | **[Coverage Report](https://html-preview.github.io/?url=https://github.com/mossy2100/PHP-Collections/blob/main/build/coverage/index.html)**

![PHP 8.4](docs/logo_php8_4.png)

## Development and Quality Assurance / AI Disclosure

[Claude Chat](https://claude.ai) and [Claude Code](https://www.claude.com/product/claude-code) were used in the development of this package. The core classes were designed, coded, and commented primarily by the author, with Claude providing substantial assistance with code review, suggesting improvements, debugging, and generating tests and documentation. All code was thoroughly reviewed by the author, and validated using industry-standard tools including [PHP_Codesniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/), [PHPStan](https://phpstan.org/) (to level 9), and [PHPUnit](https://phpunit.de/index.html) to ensure full compliance with [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards and comprehensive unit testing with 100% code coverage. This collaborative approach resulted in a high-quality, thoroughly-tested, and well-documented package delivered in significantly less time than traditional development methods.

![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)

## Why Galaxon Collections?

PHP's native arrays are powerful but have limitations:
- **Keys restricted to strings and integers** - Can't use booleans, floats, arrays, or objects as keys.
- **No type safety** - Arrays accept any mix of types without validation.
- **Type coercion issues** - Keys like `1`, `'1'`, `true`, and `1.0` all become the same key.
- **Limited operations** - Built-in array functions lack chaining, immutability, and advanced transformations. Errors and exceptions are inconsistent.

Galaxon Collections solves these problems with:

✅ **Any type as keys** - Use objects, arrays, booleans, floats, null as Dictionary keys.

✅ **Runtime type validation** - Optional type constraints with compile-time-like checking.

✅ **Rich API** - Fluent interfaces, method chaining, functional programming support.

✅ **Immutable operations** - Transformations return new collections without modifying originals.

✅ **Type inference** - Automatically detect types from your data.

✅ **Mathematical correctness** - Proper type safety for operations like sum() and product().

## Alternatives

Before using this package, you may want to check out these PHP extensions:

- [Standard PHP Library](https://www.php.net/manual/en/book.spl.php)
- [Data Structures](https://www.php.net/manual/en/book.ds.php)

These are official PHP extensions that provide efficient data structure implementations and will probably be well-supported going forward. However, if you need runtime type safety and generics-like behavior, or simply prefer a more functional style of programming, Galaxon Collections provides features that these extensions lack, including type constraints, type inference, and type-safe operations.

## Features

### Type Safety
```php
// Restrict types at runtime
$numbers = new Sequence('int');
$numbers->append(1, 2, 3);    // ✅ Works
$numbers->append('four');      // ❌ TypeError

// Union types
$mixed = new Sequence('int|string');
$mixed->append(1, 'two', 3);   // ✅ All work

// Type inference
$seq = new Sequence(source: [1, 2, 3]);
// Automatically infers type as 'int'
```

### Unrestricted Keys
```php
// PHP arrays: keys must be string|int
$array = [];
$array[new DateTime()] = 'event';  // ❌ Fatal error
$array[[1, 2]] = 'coords';         // ❌ Illegal offset

// Dictionary: any type works
$dict = new Dictionary();
$dict[new DateTime()] = 'event';         // ✅ Works
$dict[[1, 2, 3]] = 'coordinates';        // ✅ Works
$dict[fopen('file.txt', 'r')] = 'data';  // ✅ Works
$dict[true] = 'yes';                     // ✅ Works
$dict[null] = 'empty';                   // ✅ Works
```

### Functional Programming
```php
$numbers = new Sequence('int', source: [1, 2, 3, 4, 5]);

// Method chaining
$result = $numbers
    ->filter(fn($n) => $n % 2 === 0)  // Keep evens
    ->map(fn($n) => $n * 2)            // Double them
    ->reverse();                       // Reverse order

echo $result; // [10, 8, 4]

// Original unchanged (immutable operations)
echo $numbers; // [1, 2, 3, 4, 5]
```

### Set Operations
```php
$set1 = new Set('int', [1, 2, 3, 4]);
$set2 = new Set('int', [3, 4, 5, 6]);

$union = $set1->union($set2);           // {1, 2, 3, 4, 5, 6}
$intersection = $set1->intersect($set2); // {3, 4}
$difference = $set1->diff($set2);        // {1, 2}

// Subset checking
$set1->isSubsetOf($set2);      // false
$set1->isDisjointFrom($set2);  // false
```

## Installation

```bash
composer require galaxon/collections
```

**Requirements:**
- PHP 8.4 or higher
- `galaxon/core` package

## Quick Start

### Sequence - Type-safe lists
```php
use Galaxon\Collections\Sequence;

// Create with type inference
$seq = new Sequence(source: [1, 2, 3, 4, 5]);

// Add items
$seq->append(6, 7, 8);
$seq->prepend(0);

// Remove items
$item = $seq->removeByIndex(0);  // Returns removed value
$count = $seq->removeByValue(3); // Returns count removed

// Access items
echo $seq[0];        // 0
echo $seq->first();  // 0
echo $seq->last();   // 8

// Transformations
$evens = $seq->filter(fn($n) => $n % 2 === 0);
$doubled = $seq->map(fn($n) => $n * 2);
$sorted = $seq->sort();

// Aggregations
echo $seq->sum();      // 36
echo $seq->product();  // 0
echo $seq->average();  // 4.5
echo $seq->min();      // 0
echo $seq->max();      // 8
```

### Dictionary - Key-value pairs with any type
```php
use Galaxon\Collections\Dictionary;

// Create with type inference
$dict = new Dictionary(source: ['a' => 1, 'b' => 2]);

// Use objects as keys
$dict[new DateTime('2024-01-01')] = 'New Year';

// Use arrays as keys
$dict[[10, 20]] = 'coordinates';

// Type constraints
$typed = new Dictionary('string', 'int');
$typed['count'] = 42;      // ✅ Works
$typed['count'] = 'text';  // ❌ TypeError

// Check and access
if ($dict->keyExists('a')) {
    echo $dict['a'];  // 1
}

// Iteration
foreach ($dict as $key => $value) {
    echo "$key => $value\n";
}
```

### Set - Unique values
```php
use Galaxon\Collections\Set;

// Duplicates automatically removed
$set = new Set(source: [1, 2, 2, 3, 3, 3]);
echo $set->count(); // 3

// Set operations
$a = new Set('int', [1, 2, 3]);
$b = new Set('int', [2, 3, 4]);

$a->union($b);       // {1, 2, 3, 4}
$a->intersect($b);   // {2, 3}
$a->diff($b);        // {1}

// Membership testing
var_dump($set->contains(2));  // true
var_dump($set->contains(5));  // false
```

## Classes Documentation

### Base Class

#### [Collection](docs/Collection.md)
The abstract base class for all collection types, providing shared functionality and defining the common interface.

**Key Features:**
- Implements Countable, IteratorAggregate, Stringable
- Common methods: count(), empty(), clear(), all(), any()
- Type safety through TypeSet integration
- Consistent API across all collection types

**Note:** Cannot be instantiated directly - use Sequence, Dictionary, or Set.

### Core Collections

#### [Sequence](docs/Sequence.md)
A type-safe ordered list with zero-based integer indexing, similar to `List<T>` in C# or Java.

**Key Features:**
- Sequential integer indexes (0, 1, 2, ...)
- Allows duplicate values
- Maintains insertion order
- Array-like access with `[]`
- Rich transformation methods (map, filter, sort, etc.)
- Aggregation methods (sum, product, average, min, max)
- Gap-filling with default values

**Best For:**
- Ordered collections where position matters
- Lists that can contain duplicates
- When you need indexed access
- Mathematical operations on numeric sequences

#### [Dictionary](docs/Dictionary.md)
A type-safe key-value collection that accepts **any PHP type** for both keys and values.

**Key Features:**
- Any type as keys (objects, arrays, resources, scalars, null)
- Any type as values
- Type constraints for both keys and values
- Array-like access with `[]`
- No key coercion (preserves exact types)
- Transformation methods (map, filter, flip, sort)

**Best For:**
- When you need object/array/resource keys
- Associative data with type safety
- Avoiding PHP's key coercion issues
- Complex key-value mappings

#### [Set](docs/Set.md)
A type-safe collection of unique values with set operations.

**Key Features:**
- Uniqueness enforced automatically
- Set operations (union, intersection, difference)
- Subset/superset testing
- Disjoint checking
- Any value type

**Best For:**
- Removing duplicates
- Mathematical set operations
- Membership testing
- When order doesn't matter

### Supporting Classes

#### [TypeSet](docs/TypeSet.md)
Manages type constraints for collections with runtime validation.

**Key Features:**
- Flexible type specification (strings, union types, nullable)
- Runtime type checking
- Type inference from values
- Default value inference
- Support for pseudotypes (scalar, number, uint, mixed, etc.)
- Class/interface/trait matching with inheritance

**Best For:**
- Runtime type validation
- Building type-safe APIs
- Type inference in generic code

#### [KeyValuePair](docs/KeyValuePair.md)
Immutable container for a key-value pair where both can be any type.

**Key Features:**
- Readonly/immutable
- Any type for key and value
- Preserves exact types
- Simple and lightweight

**Best For:**
- Internal Dictionary storage
- Representing key-value associations in custom code
- When you need immutable pairs

## Type Safety Examples

### Type Constraints

```php
// Single type
$ints = new Sequence('int');
$ints->append(1, 2, 3);

// Union types
$mixed = new Sequence('int|string|null');
$mixed->append(1, 'hello', null);

// Nullable types
$nullable = new Sequence('?DateTime');
$nullable->append(new DateTime(), null);

// Class types
$dates = new Sequence('DateTime');
$dates->append(new DateTime());

// Pseudotypes
$scalars = new Sequence('scalar');  // int|float|string|bool
$numbers = new Sequence('number');  // int|float
$uints = new Sequence('uint');      // unsigned int (>= 0)
```

### Type Inference

```php
// Automatic type detection
$seq = new Sequence(source: [1, 2, 3]);
// Type inferred as 'int', default value inferred as 0

// Mixed types
$seq = new Sequence(source: [1, 'hello', null]);
// Types inferred as 'int|string|null'

// Dictionary inference
$dict = new Dictionary(source: ['a' => 1, 'b' => 2]);
// Key type: 'string', Value type: 'int'
```

### Runtime Validation

```php
$numbers = new Sequence('int');

try {
    $numbers->append('not a number');
} catch (TypeError $e) {
    echo $e->getMessage();
    // "Disallowed type: string."
}
```

## Advanced Features

### Default Values and Gap-Filling

```php
$seq = new Sequence('int', 99);  // Custom default

// Setting beyond current range fills gaps
$seq[5] = 10;
// Result: [99, 99, 99, 99, 99, 10]
```

### Fluent Interfaces

```php
$result = (new Sequence('int'))
    ->append(1, 2, 3, 4, 5)
    ->filter(fn($n) => $n > 2)
    ->map(fn($n) => $n * 2)
    ->sort();
// Result: [6, 8, 10]
```

### Mathematical Operations

```php
$numbers = new Sequence('number', source: [1, 2, 3, 4, 5]);

echo $numbers->sum();      // 15
echo $numbers->product();  // 120
echo $numbers->average();  // 3
echo $numbers->min();      // 1
echo $numbers->max();      // 5

// Type-safe: throws TypeError for non-numeric values
$strings = new Sequence('string', source: ['1', '2', '3']);
$strings->sum(); // TypeError!
```

### Range Generation

```php
// Integer ranges
$seq = Sequence::range(1, 10);        // [1, 2, 3, ..., 10]
$seq = Sequence::range(10, 1, -1);    // [10, 9, 8, ..., 1]

// Float ranges
$seq = Sequence::range(0.0, 1.0, 0.1); // [0.0, 0.1, 0.2, ..., 1.0]
```

### Conversion Methods

```php
// Sequence ↔ Dictionary
$seq = new Sequence('int', source: [10, 20, 30]);
$dict = $seq->toDictionary();
// Result: Dictionary with keys 0, 1, 2

// Sequence ↔ Set
$seq = new Sequence(source: [1, 2, 2, 3, 3, 3]);
$set = $seq->toSet();
// Result: Set with {1, 2, 3} (duplicates removed)

// Any collection → Array
$array = $collection->toArray();
```

## Performance Considerations

### Memory Efficiency
- Collections use internal PHP arrays for storage
- KeyValuePair adds minimal overhead (just object wrapper)
- TypeSet validation is optimized for common types

### Type Checking Overhead
- Type validation happens on write operations only
- Read operations have no type checking overhead
- Type inference scans values once during construction

## Testing

The library includes comprehensive test coverage:

```bash
# Run all tests
vendor/bin/phpunit

# Run all tests for a specific collection type
vendor/bin/phpunit tests/Dictionary

# Run specific test class
vendor/bin/phpunit tests/Sequence/SequenceTransformationTest.php

# Run with coverage (generates HTML report and clover.xml)
composer test
```

**Test Coverage:**
- 500+ tests across all classes
- 100% code coverage
- Edge cases, error conditions, and type safety

## License

MIT License - see [LICENSE](LICENSE) for details

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

For questions or suggestions, please [open an issue](https://github.com/mossy2100/PHP-Collections/issues).

## Support

- **Issues**: https://github.com/mossy2100/PHP-Collections/issues
- **Documentation**: See [docs/](docs/) directory for detailed class documentation
- **Examples**: See test files for comprehensive usage examples

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.
