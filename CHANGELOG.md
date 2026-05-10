# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.3] - 2026-05-11

### Fixed

- **`Sequence::chooseRand()`** — now returns a `list<mixed>` (values only) instead of an associative array with original indexes as keys. This makes it consistent with `removeRand()` and prevents internal array indexes from leaking into the return value.

### Documentation

- Updated `Sequence.md`: corrected return type description and example for `chooseRand()`.

---

## [1.0.2] - 2026-04-09

### Changed

- **`composer.json`** — Bumped `galaxon/core` constraint from `^1.0` to `^1.6`. This is a hard requirement: Core's traits were reorganised in v1.6.0 into `Traits/Asserts/` and `Traits/Comparison/` subnamespaces, and `Collection` imports `Galaxon\Core\Traits\Comparison\Equatable` (the new path). Older Core versions would fail to autoload.
- **`Collection.php`** — Updated `use` statement: `Galaxon\Core\Traits\Equatable` → `Galaxon\Core\Traits\Comparison\Equatable`.

### Documentation

- Updated `Equatable` trait links in `Collection.md`, `Dictionary.md`, `Sequence.md`, and `Set.md` to point at the new `Traits/Comparison/Equatable.md` path.

---

## [1.0.1] - 2026-04-02

### Changed

- **Sequence::join()** — now uses `Strings::toString()` to convert items, supporting all types including non-Stringable objects, booleans, null, enums, and arrays.
- **Exception messages** standardised across all classes to follow "Cannot X" convention:
  - Range step size, chunk size, and count messages updated.
  - Empty Sequence messages now specify the failed operation.
  - Filter/map callback messages standardised across Set and Dictionary.
  - Fixed missing article ("an") in max/average empty Sequence messages.

### Fixed

- **Sequence::join()** — no longer throws `DomainException` for non-Stringable objects. All items are now convertible to strings.

### Documentation

- Fixed incorrect exception types in TypeSet.md (`DomainException`/`RuntimeException` → `LogicException` for `getDefaultValue()`).
- Standardised Dictionary description across See Also sections ("Key-value collection").
- Added inherited `items` and `valueTypes` properties to Sequence, Dictionary, and Set docs with links to Collection.md.
- Removed Contributing section from README (link moved to Support).

---

## [1.0.0] - 2026-01-05

### First Stable Release

This is the first stable release of Galaxon Collections, ready for publication on Packagist.

### Breaking Changes

- **Exception types standardized** - All exceptions now use SPL exception types consistently:
  - `TypeError` → `InvalidArgumentException` (wrong type passed)
  - `ValueError` → `DomainException` (invalid value)
  - `RuntimeException` → `OutOfBoundsException` (duplicate keys in flip/map)
  - `UnderflowException` → `LengthException` (empty collection operations)
  - `OutOfRangeException` → `LengthException` (empty sequence first/last/min/max)
  - Added `UnexpectedValueException` for callback return type errors

- **Dictionary::filter()** - Callback signature changed from `fn($key, $value)` to `fn($pair)` for consistency with other Dictionary methods

### Changed

- **composer.json** - Updated for Packagist publication:
  - Added keywords for discoverability
  - Added author information
  - Added homepage and support URLs
  - Updated dependencies to use Packagist versions (galaxon/core ^1.0)
  - Improved description

### Documentation

- Updated all class documentation to reflect new exception types
- Updated Dictionary filter() examples to use new callback signature

---

## [0.3.0] - 2025-12-08

### Changed
- **BREAKING:** Renamed `equals()` method to `equal()` across all collection classes
  - Affects `Collection`, `Dictionary`, `Sequence`, and `Set`
  - Updated to use `Equatable` trait instead of implementing interface
- **BREAKING:** Renamed Set comparison methods (removed `is` prefix):
  - `isSubsetOf()` → `subset()`
  - `isProperSubsetOf()` → `properSubset()`
  - `isSupersetOf()` → `superset()`
  - `isProperSupersetOf()` → `properSuperset()`
  - `isDisjointFrom()` → `disjoint()`
- **BREAKING:** Refactored Sequence default value handling
  - Removed `$defaultValue` parameter from constructor
  - Changed from `tryInferDefaultValue()` to `getDefaultValue()` in TypeSet
  - Default values now automatically determined when filling gaps
  - Throws `RuntimeException` if default value cannot be inferred

### Removed
- **BREAKING:** Removed `uint` pseudotype support from TypeSet
  - Removed from all documentation and examples
  - Use `int` with validation instead
- Removed `DuplicateKeyException` class
  - `Dictionary::flip()` and `Dictionary::map()` now throw `RuntimeException` for duplicate key scenarios

### Documentation
- Updated all documentation to reflect method name changes
- Updated examples throughout to use new method names
- Removed `uint` references from TypeSet documentation

---

## [0.2.0] - 2025-01-15

### Added
- **Dictionary::map()** - Transform key-value pairs using a callback function
  - Maps each Pair to a new Pair, allowing transformation of both keys and values
  - Automatically infers types from callback results
  - Throws `RuntimeException` if callback produces duplicate keys
  - Returns new Dictionary without modifying original

### Changed
- **Dictionary::flip()** - Now throws `RuntimeException` instead of `ValueError` for duplicate values
- **Dictionary::map()** - Now throws `RuntimeException` instead of `ValueError` for duplicate keys

### Documentation
- Added comprehensive documentation for `Dictionary::map()` method in Dictionary.md
- Updated README with new Dictionary methods
- Added test coverage for all new functionality

---

## [0.1.0] - 2025-01-14

### Added
- First version of Galaxon Collections library
- **Collection** - Abstract base class for all collection types
  - Implements Countable, IteratorAggregate, Stringable
  - Common methods: `count()`, `empty()`, `clear()`, `all()`, `any()`
  - Type safety through TypeSet integration
- **Sequence** - Type-safe ordered list with zero-based integer indexing
  - Sequential integer indexes (0, 1, 2, ...)
  - Array-like access with `[]`
  - Transformation methods: `map()`, `filter()`, `sort()`, `reverse()`, `unique()`, etc.
  - Aggregation methods: `sum()`, `product()`, `average()`, `min()`, `max()`
  - Range generation with `Sequence::range()`
  - Gap-filling with default values
- **Dictionary** - Type-safe key-value collection with unrestricted key types
  - Support for any PHP type as keys (objects, arrays, resources, scalars, null)
  - Array-like access with `[]`
  - No key coercion (preserves exact types)
  - Methods: `flip()`, `merge()`, `sortByKey()`, `sortByValue()`
  - Factory method `Dictionary::combine()` for creating from separate key/value iterables
- **Set** - Type-safe collection of unique values
  - Automatic duplicate removal
  - Set operations: `union()`, `intersect()`, `diff()`
  - Subset/superset testing: `subset()`, `properSubset()`, `superset()`, `properSuperset()`
  - Disjoint checking: `disjoint()`
- **TypeSet** - Runtime type validation and management
  - Flexible type specification (strings, union types, nullable)
  - Type inference from values
  - Default value inference
  - Support for pseudotypes (scalar, number, uint, mixed, etc.)
  - Class/interface/trait matching with inheritance support
- **Pair** - Immutable container for key-value pairs
  - Readonly/immutable
  - Support for any type as key and value
- Runtime type validation with detailed error messages
- Type inference from source data
- Fluent interfaces and method chaining
- Immutable operations (transformations return new collections)
- Comprehensive test suite with 500+ tests and 100% code coverage
- Full PHPDoc documentation
- PSR-12 coding standards compliance
- PHPStan level 9 static analysis compliance

### Requirements
- PHP 8.4 or higher
- galaxon/core package
