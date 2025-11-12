# TypeSet

A type-safe set of type names used internally by collections to enforce type constraints at runtime.

## Overview

TypeSet is a utility class that manages a set of allowed type names for collection values. It provides runtime type validation, type inference, and default value inference for PHP's type system.

While primarily used internally by Sequence, Dictionary, Set, and Collection classes, TypeSet can be useful in your own code when you need flexible runtime type checking.

## Features

- **Flexible type specification**: Strings, union types, nullable types, arrays of types
- **Type validation**: Runtime checking of values against type constraints
- **Type inference**: Automatically detect types from values
- **Default value inference**: Smart defaults for common types
- **Pseudotypes**: Support for scalar, number, uint, iterable, callable, mixed
- **Class types**: Classes, interfaces, traits (including inheritance)
- **Resource types**: Specific resource types like 'resource (stream)'

## Constructor

### __construct()

```php
public function __construct(string|iterable|null $types = null)
```

Create a TypeSet with optional type specifications.

**Type Specification Options:**

- `null` - Empty TypeSet (any type allowed)
- `string` - Single type or union type syntax (e.g., `'int'`, `'int|string'`, `'?int'`)
- `iterable` - Array of type names (e.g., `['int', 'string']`)

**Examples:**
```php
// Empty TypeSet (allows any type)
$ts = new TypeSet();

// Single type
$ts = new TypeSet('int');

// Union type with | syntax
$ts = new TypeSet('int|string|null');

// Nullable type with ? syntax
$ts = new TypeSet('?DateTime');
// Equivalent to: new TypeSet('DateTime|null')

// Array of types
$ts = new TypeSet(['int', 'string', 'bool']);

// Class types
$ts = new TypeSet('DateTime');
$ts = new TypeSet('MyNamespace\MyClass');

// Interface types
$ts = new TypeSet('Countable');

// Pseudotypes
$ts = new TypeSet('scalar');   // int|float|string|bool
$ts = new TypeSet('number');   // int|float
$ts = new TypeSet('uint');     // unsigned int (>= 0)
$ts = new TypeSet('mixed');    // Any type
```

## Type Checking Methods

### match()

```php
public function match(mixed $value): bool
```

Check if a value matches one of the types in the TypeSet. Returns `true` if the value is allowed, `false` otherwise.

**Examples:**
```php
$ts = new TypeSet('int|string');

var_dump($ts->match(42));      // true
var_dump($ts->match('hello')); // true
var_dump($ts->match(3.14));    // false
var_dump($ts->match(true));    // false

// Pseudotype matching
$ts = new TypeSet('scalar');
var_dump($ts->match(42));      // true (int is scalar)
var_dump($ts->match('text'));  // true (string is scalar)
var_dump($ts->match(3.14));    // true (float is scalar)
var_dump($ts->match(true));    // true (bool is scalar)
var_dump($ts->match([]));      // false (array is not scalar)

// Number pseudotype
$ts = new TypeSet('number');
var_dump($ts->match(42));      // true
var_dump($ts->match(3.14));    // true
var_dump($ts->match('42'));    // false (string, not number)

// Uint pseudotype
$ts = new TypeSet('uint');
var_dump($ts->match(0));       // true
var_dump($ts->match(42));      // true
var_dump($ts->match(-1));      // false (negative)

// Class matching (with inheritance)
$ts = new TypeSet('DateTime');
var_dump($ts->match(new DateTime()));           // true
var_dump($ts->match(new DateTimeImmutable()));  // true (subclass)

// Interface matching
$ts = new TypeSet('Countable');
var_dump($ts->match(new ArrayObject()));  // true
var_dump($ts->match([1, 2, 3]));         // false (arrays aren't objects)
```

### check()

```php
public function check(mixed $value, string $label = ''): void
```

Check if a value matches the types. Throws `TypeError` if the value doesn't match.

**Examples:**
```php
$ts = new TypeSet('int');

$ts->check(42);        // OK, no exception
$ts->check('hello');   // TypeError: Disallowed type: string.

// With custom label
$ts->check(42, 'age');        // OK
$ts->check('text', 'age');    // TypeError: Disallowed age type: string.
```

## Type Management Methods

### add()

```php
public function add(string|iterable $types): self
```

Add one or more types to the TypeSet. Returns `$this` for chaining.

**Examples:**
```php
$ts = new TypeSet();

// Add single type
$ts->add('int');

// Add union type
$ts->add('string|bool');

// Add nullable type
$ts->add('?float');

// Add array of types
$ts->add(['array', 'object']);

// Chaining
$ts = new TypeSet()
    ->add('int')
    ->add('string')
    ->add('bool');

echo $ts; // {int, string, bool}
```

### addValueType()

```php
public function addValueType(mixed $value): self
```

Infer the type from a value and add it to the TypeSet. Returns `$this` for chaining.

**Examples:**
```php
$ts = new TypeSet();

$ts->addValueType(42);              // Adds 'int'
$ts->addValueType('hello');         // Adds 'string'
$ts->addValueType(3.14);            // Adds 'float'
$ts->addValueType(null);            // Adds 'null'
$ts->addValueType(new DateTime());  // Adds 'DateTime'

echo $ts; // {int, string, float, null, DateTime}

// Use case: build TypeSet from array values
$ts = new TypeSet();
foreach ([1, 'two', 3.0, true, null] as $value) {
    $ts->addValueType($value);
}
echo $ts; // {int, string, float, bool, null}
```

## Inspection Methods

### contains()

```php
public function contains(string $type): bool
```

Check if the TypeSet contains a specific type.

**Examples:**
```php
$ts = new TypeSet('int|string|bool');

var_dump($ts->contains('int'));    // true
var_dump($ts->contains('string')); // true
var_dump($ts->contains('float'));  // false
```

### containsAll()

```php
public function containsAll(string ...$types): bool
```

Check if the TypeSet contains all the given types.

**Examples:**
```php
$ts = new TypeSet('int|string|bool');

var_dump($ts->containsAll('int', 'string'));        // true
var_dump($ts->containsAll('int', 'string', 'bool')); // true
var_dump($ts->containsAll('int', 'float'));         // false
```

### containsAny()

```php
public function containsAny(string ...$types): bool
```

Check if the TypeSet contains any of the given types.

**Examples:**
```php
$ts = new TypeSet('int|string');

var_dump($ts->containsAny('int', 'float'));    // true (has int)
var_dump($ts->containsAny('string', 'bool'));  // true (has string)
var_dump($ts->containsAny('float', 'bool'));   // false
```

### containsOnly()

```php
public function containsOnly(string ...$types): bool
```

Check if the TypeSet contains exactly the given types (no more, no less).

**Examples:**
```php
$ts = new TypeSet('int|string');

var_dump($ts->containsOnly('int', 'string'));        // true
var_dump($ts->containsOnly('string', 'int'));        // true (order doesn't matter)
var_dump($ts->containsOnly('int'));                  // false (missing string)
var_dump($ts->containsOnly('int', 'string', 'bool')); // false (extra type)
```

### empty()

```php
public function empty(): bool
```

Check if the TypeSet is empty.

**Examples:**
```php
$ts1 = new TypeSet();
var_dump($ts1->empty()); // true

$ts2 = new TypeSet('int');
var_dump($ts2->empty()); // false
```

### anyOk()

```php
public function anyOk(): bool
```

Check if the TypeSet allows values of any type (either empty or contains 'mixed').

**Examples:**
```php
$ts1 = new TypeSet();
var_dump($ts1->anyOk()); // true (empty allows any)

$ts2 = new TypeSet('mixed');
var_dump($ts2->anyOk()); // true

$ts3 = new TypeSet('int|string');
var_dump($ts3->anyOk()); // false
```

### nullOk()

```php
public function nullOk(): bool
```

Check if the TypeSet allows null values.

**Examples:**
```php
$ts1 = new TypeSet('?int');
var_dump($ts1->nullOk()); // true

$ts2 = new TypeSet('int|null');
var_dump($ts2->nullOk()); // true

$ts3 = new TypeSet();
var_dump($ts3->nullOk()); // true (empty allows any, including null)

$ts4 = new TypeSet('int|string');
var_dump($ts4->nullOk()); // false
```

## Default Value Inference

### tryInferDefaultValue()

```php
public function tryInferDefaultValue(mixed &$default_value): bool
```

Try to infer a sensible default value based on the types in the TypeSet. Returns `true` if successful, `false` otherwise. The inferred value is set via the reference parameter.

**Inference Rules (in priority order):**

1. `null` or contains 'null' → `null`
2. `bool` → `false`
3. `int`, `uint`, `number`, or `scalar` → `0`
4. `float` → `0.0`
5. `string` → `''` (empty string)
6. `array` or `iterable` → `[]` (empty array)
7. Other types (classes, resources, etc.) → returns `false`

**Examples:**
```php
$ts = new TypeSet('int');
$result = $ts->tryInferDefaultValue($default);
// $result = true, $default = 0

$ts = new TypeSet('string');
$result = $ts->tryInferDefaultValue($default);
// $result = true, $default = ''

$ts = new TypeSet('?int');
$result = $ts->tryInferDefaultValue($default);
// $result = true, $default = null (null has priority)

$ts = new TypeSet('array');
$result = $ts->tryInferDefaultValue($default);
// $result = true, $default = []

$ts = new TypeSet('DateTime');
$result = $ts->tryInferDefaultValue($default);
// $result = false (can't infer default for objects)

// Use case: Sequence uses this to infer default values
$ts = new TypeSet('int');
if ($ts->tryInferDefaultValue($default)) {
    echo "Default value: $default"; // Default value: 0
}
```

## Supported Type Names

### Basic Types
- `null` - Null values
- `bool` - Boolean values
- `int` - Integer values
- `float` - Floating point values
- `string` - String values
- `array` - Array values
- `object` - Any object
- `resource` - Any resource

### Pseudotypes
- `mixed` - Any type (no restrictions)
- `scalar` - String, int, float, or bool
- `number` - Int or float
- `uint` - Unsigned integer (int >= 0)
- `iterable` - Arrays, iterators, generators
- `callable` - Functions, methods, closures

### Resource Types
Must be specified in the format returned by `get_debug_type()`:
- `resource (stream)`
- `resource (curl)`
- etc.

### Class Types
- Class names: `DateTime`, `MyNamespace\MyClass`
- Interface names: `Countable`, `JsonSerializable`
- Trait names: `MyTrait`
- Leading backslashes are optional and will be stripped

**Inheritance Support:**
Values are matched against parent classes, interfaces, and traits:

```php
$ts = new TypeSet('DateTime');
$ts->match(new DateTimeImmutable()); // true (subclass)

$ts = new TypeSet('Countable');
$ts->match(new ArrayObject());       // true (implements interface)
```

## Utility Methods

### count()

```php
public function count(): int
```

Get the number of types in the TypeSet.

**Example:**
```php
$ts = new TypeSet('int|string|bool');
echo $ts->count(); // 3
```

### __toString()

```php
public function __toString(): string
```

Get a string representation of the TypeSet using set notation.

**Example:**
```php
$ts = new TypeSet('int|string');
echo $ts; // {int, string}
```

### getIterator()

```php
public function getIterator(): Traversable
```

Get an iterator for foreach loops.

**Example:**
```php
$ts = new TypeSet('int|string|bool');

foreach ($ts as $type) {
    echo $type . "\n";
}
// Output:
// int
// string
// bool
```

## Practical Examples

### Validating function arguments
```php
function processValue(mixed $value, TypeSet $allowedTypes): void
{
    $allowedTypes->check($value);
    // Process the value...
}

$types = new TypeSet('int|string');
processValue(42, $types);      // OK
processValue('hello', $types); // OK
processValue(3.14, $types);    // TypeError
```

### Building type constraints from data
```php
$data = [1, 2, 'hello', 3.14, null];

$ts = new TypeSet();
foreach ($data as $value) {
    $ts->addValueType($value);
}

echo $ts; // {int, string, float, null}
```

### Type-safe configuration
```php
class Config
{
    private TypeSet $allowedTypes;
    private mixed $value;

    public function __construct(string $types)
    {
        $this->allowedTypes = new TypeSet($types);
    }

    public function setValue(mixed $value): void
    {
        $this->allowedTypes->check($value, 'config value');
        $this->value = $value;
    }
}

$config = new Config('int|string');
$config->setValue(42);      // OK
$config->setValue('text');  // OK
$config->setValue(true);    // TypeError: Disallowed config value type: bool.
```
