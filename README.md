# OceanMoon PHP Collections

Type-safe collection classes for PHP 8.4+.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Documentation](docs/)**

![PHP 8.4](docs/logo_php8_4.png)

---

## Description

A type-safe collection library featuring runtime type validation, immutable operations, and unrestricted key types.

**Core Classes:**
- **Sequence** - Ordered lists with integer indexing
- **Dictionary** - Key-value pairs accepting any type as keys
- **Set** - Unique value collections with set operations

---

## Development and Quality Assurance

[Claude Chat](https://claude.ai) and [Claude Code](https://www.claude.com/product/claude-code) were used in the development of this package. The core classes were designed, coded, and commented primarily by the author, with Claude providing substantial assistance with code review, suggesting improvements, debugging, and generating tests and documentation. All code was thoroughly reviewed by the author, and validated using industry-standard tools including [PHP_Codesniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/), [PHPStan](https://phpstan.org/) (to level 9), and [PHPUnit](https://phpunit.de/index.html) to ensure full compliance with [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards and comprehensive unit testing with 100% code coverage. This collaborative approach has produced a well-designed, production-ready package with thorough test coverage and documentation.

![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)

---

## Why OceanMoon Collections?

PHP's native arrays are powerful but have limitations:
- **Keys restricted to strings and integers** - Can't use booleans, floats, arrays, or objects as keys.
- **No type safety** - Arrays accept any mix of types without validation.
- **Type coercion issues** - Keys like `1`, `'1'`, `true`, and `1.0` all become the same key.
- **Limited operations** - Built-in array functions lack chaining, immutability, and advanced transformations. Errors and exceptions are inconsistent.

OceanMoon Collections solves these problems with:

✅ **Any type as keys** - Use objects, arrays, booleans, floats, null as Dictionary keys.

✅ **Runtime type validation** - Optional type constraints with compile-time-like checking.

✅ **Rich API** - Fluent interfaces, method chaining, functional programming support.

✅ **Immutable operations** - Transformations return new collections without modifying originals.

✅ **Type inference** - Automatically detect types from your data.

✅ **Mathematical correctness** - Proper type safety for operations like sum() and product().

---

## Alternatives

Before using this package, you may want to check out these PHP extensions:

- [Standard PHP Library](https://www.php.net/manual/en/book.spl.php)
- [Data Structures](https://www.php.net/manual/en/book.ds.php)

These are official PHP extensions that provide efficient data structure implementations and will probably be well-supported going forward. However, if you need runtime type safety and generics-like behavior, or simply prefer a more functional style of programming, OceanMoon Collections provides features that these extensions lack, including type constraints, type inference, and type-safe operations.

---

## Features

### Type Safety
```php
// Restrict types at runtime
$numbers = new Sequence('int');
$numbers->append(1, 2, 3);    // ✅ Works
$numbers->append('four');      // ❌ InvalidArgumentException

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
$set1->subset($set2);      // false
$set1->disjoint($set2);  // false
```

---

## Installation

```bash
composer require oceanmoon/collections
```

---

## Requirements

- PHP ^8.4
- oceanmoon/core

---

## Quick Start

### Sequence - Type-safe lists

```php
use OceanMoon\Collections\Sequence;

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
use OceanMoon\Collections\Dictionary;

// Create with type inference
$dict = new Dictionary(source: ['a' => 1, 'b' => 2]);

// Use objects as keys
$dict[new DateTime('2024-01-01')] = 'New Year';

// Use arrays as keys
$dict[[10, 20]] = 'coordinates';

// Type constraints
$typed = new Dictionary('string', 'int');
$typed['count'] = 42;      // ✅ Works
$typed['count'] = 'text';  // ❌ InvalidArgumentException

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
use OceanMoon\Collections\Set;

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

---

## Classes

### Core Collections

- **[Collection](docs/Collection.md)** - Abstract base class providing shared functionality for all collection types
- **[Sequence](docs/Sequence.md)** - Ordered lists with integer indexing, similar to `List<T>` in C# or Java
- **[Dictionary](docs/Dictionary.md)** - Key-value pairs accepting any PHP type for both keys and values
- **[Set](docs/Set.md)** - Unique value collections with mathematical set operations

### Supporting Classes

- **[TypeSet](docs/TypeSet.md)** - Runtime type validation and constraint management
- **[Pair](docs/Pair.md)** - Immutable key-value pair container

---

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

---

## License

MIT License - see [LICENSE](LICENSE) for details

---

## Support

- **Issues**: https://github.com/mossy2100/PHP-Collections/issues
- **Documentation**: See [docs/](docs/) directory for detailed class documentation
- **Examples**: See test files for comprehensive usage examples

For questions or suggestions, please [open an issue](https://github.com/mossy2100/PHP-Collections/issues).

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.
