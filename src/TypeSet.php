<?php

declare(strict_types = 1);

namespace Galaxon\Collections;

// Interfaces
use ArrayIterator;
use Countable;
use Galaxon\Core\Numbers;
use Galaxon\Core\Types;
use IteratorAggregate;
use RuntimeException;
use Traversable;
use TypeError;
use ValueError;

// Throwables

// Other

// Galaxon

/**
 * Encapsulates a set of types, represented as strings.
 *
 * ## Supported Type Specifications
 *
 * ### Basic Types
 * - 'null' - Null values
 * - 'bool' - Boolean values
 * - 'int' - Integer values
 * - 'float' - Floating point values
 * - 'string' - String values
 * - 'array' - Array values
 * - 'object' - Any object instance
 * - 'resource' - Any resource type
 *
 * ### Pseudotypes
 * - 'mixed' - Any type (no restrictions)
 * - 'scalar' - Any scalar type (string|int|float|bool)
 * - 'iterable' - Arrays, iterators, generators (anything iterable)
 * - 'callable' - Functions, methods, closures, invokables
 *
 * ### Custom pseudotypes (invented for this package)
 * - 'number' - Either number type (int|float)
 * - 'uint' - Unsigned integer type (int where value >= 0)
 *
 * ### Resource types
 * Examples: 'resource (stream)', 'resource (curl)', etc.
 * These must be given as the "resource (xxx)" format, as returned from get_debug_type(), to distinguish them from class names.
 *
 * ### Class, interface and trait names
 * - Class names: 'DateTime', 'MyNameSpace\MyClass' (leading '\' optional; includes inheritance)
 * - Interface names: 'Countable', 'JsonSerializable' (includes implementations)
 * - Leading backslashes can be included, but will be stripped; e.g. \DateTime is stored and matched as DateTime.
 * - Values matched against a class name will also match parent classes, interfaces, and traits.
 *
 * ### Union Types
 * Use pipe syntax to allow multiple types:
 * - 'string|int' - String OR integer values
 * - 'array|object' - Array OR object values
 * - 'DateTime|null' - DateTime objects OR null
 *
 * ### Nullable Types
 * Use question mark syntax for nullable types:
 * - '?string' - Equivalent to 'string|null'
 * - '?DateTime' - Equivalent to 'DateTime|null'
 *
 * ### Unsupported pseudo-types: void, never, false, true, self, static.
 */
class TypeSet implements Countable, IteratorAggregate
{
    // region Properties

    /**
     * Items in the set.
     *
     * @var array<string>
     */
    private(set) array $types = [];

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param string|iterable|null $types The types to add to the TypeSet.
     * @throws TypeError If type are not provided as strings.
     * @throws ValueError If a type name is invalid.
     */
    public function __construct(string|iterable|null $types = null)
    {
        // If some types have been provided, add them to the TypeSet.
        if ($types !== null) {
            $this->add($types);
        }
    }

    // endregion

    // region Main API

    /**
     * Check if a value matches one of the types in the TypeSet.
     *
     * @see https://www.php.net/manual/en/function.get-debug-type.php
     *
     * @param mixed $value The value to check.
     * @return bool True if the value's type matches one of the types in the TypeSet, false otherwise.
     */
    public function match(mixed $value): bool
    {
        // Check if any type is allowed.
        if ($this->anyOk()) {
            return true;
        }

        // Check for an exact type or class name match.
        // This will match any string returned by get_debug_type(), including "null", resource type strings like
        // "resource (stream)", and class names.
        // Note that get_debug_type() returns class names with the full namespace but without a leading backslash,
        // which is partly why we remove the leading backslash when adding types to the TypeSet.
        // It will not match old type names like "integer", "double", or "boolean".
        if ($this->contains(get_debug_type($value))) {
            return true;
        }

        // Check scalar.
        if ($this->contains('scalar') && is_scalar($value)) {
            return true;
        }

        // Check number.
        if ($this->contains('number') && Types::isNumber($value)) {
            return true;
        }

        // Check uint.
        if ($this->contains('uint') && Types::isUint($value)) {
            return true;
        }

        // Check iterable.
        if ($this->contains('iterable') && is_iterable($value)) {
            return true;
        }

        // Check callable.
        if ($this->contains('callable') && is_callable($value)) {
            return true;
        }

        // Check resource (unspecified type).
        if ($this->contains('resource') && is_resource($value)) {
            return true;
        }

        // Additional checks for objects.
        if (is_object($value)) {
            // Check for any object type.
            if ($this->contains('object')) {
                return true;
            }

            // Check value against parent classes, interfaces, and traits.
            foreach ($this->types as $type) {
                // Check for a matching class or interface.
                // By using 'instanceof' here instead of comparing the result of get_class() or get_debug_type() with
                // the type names in the TypeSet, we can also match on parent classes and interfaces, which is what we want.
                if ((class_exists($type) || interface_exists($type)) && $value instanceof $type) {
                    return true;
                }

                // Check for a matching trait.
                if (trait_exists($type) && Types::usesTrait($value, $type)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if the given value matches the set of types.
     *
     * @param mixed $value The value to check.
     * @throws TypeError If the type is not allowed by the TypeSet.
     */
    public function check(mixed $value, string $label = ''): void
    {
        if (!$this->match($value)) {
            $msg = 'Disallowed ' . ($label ? $label . ' ' : '') . 'type: ' . get_debug_type($value) . '.';
            throw new TypeError($msg);
        }
    }

    /**
     * Try to get a sane default value for this type set.
     *
     * @param mixed $default_value The default value.
     * @return bool True if a default value was found, false otherwise.
     */
    public function tryGetDefaultValue(mixed &$default_value): bool
    {
        $result = true;
        if ($this->nullOk()) {
            $default_value = null;
        }
        elseif ($this->contains('bool')) {
            $default_value = false;
        }
        elseif ($this->containsAny('int', 'uint', 'number', 'scalar')) {
            $default_value = 0;
        }
        elseif ($this->contains('float')) {
            $default_value = 0.0;
        }
        elseif ($this->contains('string')) {
            $default_value = '';
        }
        elseif ($this->containsAny('array', 'iterable')) {
            $default_value = [];
        }
        else {
            $result = false;
        }

        return $result;
    }

    // endregion

    // region Type name validation (private static methods)

    /**
     * Normalize a type name by removing leading and trailing whitespace, and a leading backslash if present.
     *
     * We strip the leading backslash so that contains() work correctly if the type name contains a leading backslash
     * either when added to the TypeSet or provided to the method.
     *
     * @param string $type The type name to normalize.
     * @return string The normalized type name.
     */
    private static function normalizeTypeName(string $type): string {
        return ltrim(trim($type), '\\');
    }

    /**
     * Check if the given type is a basic type.
     *
     * @param string $type The type to check.
     * @return bool True if the type is a basic type, false otherwise.
     */
    private static function isBasicType(string $type): bool {
        $types = ['null', 'int', 'float', 'string', 'bool', 'array', 'object', 'resource'];
        return in_array($type, $types, true);
    }

    /**
     * Check if the given type is a pseudotype.
     *
     * @param string $type The type to check.
     * @return bool True if the type is a pseudotype, false otherwise.
     */
    private static function isPseudoType(string $type): bool {
        $types = ['callable', 'iterable', 'mixed', 'scalar', 'number', 'uint'];
        return in_array($type, $types, true);
    }

    /**
     * Check if the given type name looks like a valid resource type.
     *
     * The string must match the result of get_debug_type() NOT gettype() or get_resource_type().
     * @see https://www.php.net/manual/en/function.get-debug-type.php
     * @see https://www.php.net/manual/en/resource.php
     *
     * @param string $type The type to check.
     * @return bool True if the type is a resource type, false otherwise.
     */
    private static function isResourceType(string $type): bool {
        $ok = preg_match("/^resource \([\w. ]+\)$/", $type);

        if ($ok === false) {
            $error = preg_last_error_msg();
            throw new RuntimeException("PCRE error when testing for valid resource type name: $error");
        }

        return $ok === 1;
    }

    /**
     * Check if the given type name looks like a valid class name.
     *
     * NB: This will NOT match anonymous class names as returned from get_debug_type() or get_class().
     * It does permit a leading backslash, even though it may never be called with a type name that has one.
     *
     * @see https://www.php.net/manual/en/language.oop5.basic.php
     *
     * @param string $type The type to check.
     * @return bool True if the type is a class name, false otherwise.
     */
    private static function isClassType(string $type): bool {
        $class_name_part = "[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*";
        $ok = preg_match("/^\\\\?($class_name_part)(?:\\\\$class_name_part)*$/", $type);

        if ($ok === false) {
            $error = preg_last_error_msg();
            throw new RuntimeException("PCRE error when testing for valid class name: $error");
        }

        return $ok === 1;
    }

    /**
     * Checks if the provided string looks like a valid type. That includes core types, pseudotypes, resource types,
     * and classes.
     *
     * NB: The provided type name should already be normalized with normalizeTypeName().
     *
     * @param string $type The type to check.
     * @return bool True if the type is a valid type, false otherwise.
     */
    private static function isValid(string $type): bool
    {
        return !empty($type) &&
               (self::isBasicType($type) || self::isPseudoType($type) || self::isResourceType($type) ||
                self::isClassType($type));
    }

    // endregions

    // region Add methods

    /**
     * Add a type to the TypeSet.
     *
     * This is a private helper method called from add().
     *
     * @param string $type The type to add to the set.
     * @return $this The modified set.
     * @throws ValueError If the type name is invalid.
     * @see TypeSet::isValid()
     */
    private function _add(string $type): self
    {
        // Trim whitespace and leading backslash.
        $type = self::normalizeTypeName($type);

        // Check if the type string is valid. This isn't bulletproof, but it will prevent most incorrect strings.
        if (!self::isValid($type)) {
            throw new ValueError("Invalid type: $type.");
        }

        // Add the type if new.
        if (!in_array($type, $this->types, true)) {
            $this->types[] = $type;
        }

        // Return $this for chaining.
        return $this;
    }

    /**
     * Add types to the TypeSet.
     *
     * @param string|iterable $types The types to add to the TypeSet.
     * @return $this The modified TypeSet.
     * @throws TypeError If a type is not provided as a string.
     * @throws ValueError If a type name is invalid.
     */
    public function add(string|iterable $types): self
    {
        // Convert a type string, including union type syntax (e.g. 'string|int'), into an array of type names.
        if (is_string($types)) {
            $types = explode('|', $types);
        }

        // Add types to the set.
        foreach ($types as $type) {
            // Check the type.
            if (!is_string($type)) {
                throw new TypeError("Types must be provided as strings.");
            }

            // Trim in case the user did something like 'string | int'.
            // Even thought _add() will trim the type, we need to do this before checking if the question mark notation
            // has been used.
            $type = trim($type);

            // Check for question mark nullable notation (e.g. '?string').
            if (strlen($type) > 1 && $type[0] === '?') {
                // Add null and the type being made nullable.
                $this->_add('null');
                $this->_add(substr($type, 1));
            }
            else {
                // Add the type.
                $this->_add($type);
            }
        }

        // Return $this for chaining.
        return $this;
    }

    /**
     * Get the type name from a value and add it to the TypeSet.
     *
     * @param mixed $value The value to get the type name from.
     * @return $this The modified set.
     */
    public function addValueType(mixed $value): self
    {
        return $this->_add(get_debug_type($value));
    }

    // endregion

    // region Inspection methods (return bool)

    /**
     * Check if the TypeSet contains the given type.
     *
     * @param string $type The type to check for.
     * @return bool If the TypeSet contains the given type.
     */
    public function contains(string $type): bool
    {
        return in_array(self::normalizeTypeName($type), $this->types, true);
    }

    /**
     * Check if the set contains all the given types.
     *
     * @param string ...$types The types to check for.
     * @return bool If the set contains all the given types.
     */
    public function containsAll(string ...$types): bool
    {
        return array_all($types, fn($type) => $this->contains($type));
    }

    /**
     * Check if the set contains any of the given types.
     *
     * @param string ...$types The types to check for.
     * @return bool If the set contains any of the given types.
     */
    public function containsAny(string ...$types): bool
    {
        return array_any($types, fn($type) => $this->contains($type));
    }

    /**
     * Check if the set is empty.
     *
     * @return bool True if the set is empty, false otherwise.
     */
    public function empty(): bool
    {
        return empty($this->types);
    }

    /**
     * Check if the TypeSet allows values of any type.
     *
     * @return bool True if the TypeSet allows values of any types, false otherwise.
     */
    public function anyOk(): bool {
        return $this->empty() || $this->contains('mixed');
    }

    /**
     * Check if the TypeSet allows nulls.
     *
     * @return bool True if the TypeSet allows nulls, false otherwise.
     */
    public function nullOk(): bool {
        return $this->contains('null') || $this->anyOk();
    }

    // endregion

    // region Countable implementation

    /**
     * Get the number of types in the set.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->types);
    }

    // endregion

    // region IteratorAggregate implementation

    /**
     * Get iterator for foreach loops.
     *
     * @return Traversable The iterator.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->types);
    }

    // endregion
}
