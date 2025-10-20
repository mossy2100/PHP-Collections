<?php

declare(strict_types = 1);

namespace Galaxon\Collections;

use Galaxon\Math\Stringify;
use Stringable;
use TypeError;
use ValueError;
use JsonException;
use Galaxon\Math\Double;

class Type
{
    // region Miscellaneous

    /**
     * Get the simple type of a value.
     *
     * Result will be one of:
     * - null
     * - bool
     * - int
     * - float
     * - string
     * - array
     * - resource
     * - object
     * - unknown
     *
     * @param mixed $value The value to get the type of.
     * @return string The simple type of the value.
     */
    public static function getSimpleType(mixed $value): string {
        // Try get_debug_type first as this returns the new, canonical type names.
        $type = get_debug_type($value);
        if (in_array($type, ['null', 'bool', 'int', 'float', 'string', 'array'], true)) {
            return $type;
        }

        // In theory, this should only ever return "object" or "resource", although "unknown" may also be possible,
        // based on the documentation for gettype(). The documentation for get_debug_type() has no equivalent.
        return explode(' ', gettype($value))[0];
    }

    // endregion

    // region Traits

    /**
     * Check if an object or class uses a given trait.
     * Handle both class names and objects, including trait inheritance.
     */
    public static function usesTrait(object|string $obj_or_class, string $trait): bool
    {
        $all_traits = self::getTraitsRecursive($obj_or_class);
        return in_array($trait, $all_traits, true);
    }

    /**
     * Get all traits used by an object or class, including parent classes and trait inheritance.
     */
    private static function getTraitsRecursive(object|string $obj_or_class): array
    {
        // Get class name.
        $class = is_object($obj_or_class) ? get_class($obj_or_class) : $obj_or_class;

        // Collection for traits.
        $traits = [];

        // Get traits from current class and all parent classes.
        do {
            $class_traits = class_uses($class);
            $traits       = array_merge($traits, $class_traits);

            // Also get traits used by the traits themselves.
            foreach ($class_traits as $trait) {
                $trait_traits = self::getTraitsRecursive($trait);
                $traits       = array_merge($traits, $trait_traits);
            }
        } while ($class = get_parent_class($class));

        return array_unique($traits);
    }

    // endregion

    // region Method for converting values into unique strings for use as keys.

    /**
     * Convert any PHP value into a unique string.
     *
     * @param mixed $value The value to convert.
     * @return string The unique string key.
     */
    public static function getStringKey(mixed $value): string
    {
        $type = get_debug_type($value);
        $result = '';

        // Core types.
        switch ($type) {
            case 'null':
                $result = 'n';
                break;

            case 'bool':
                $result = 'b:' . ($value ? 'T' : 'F');
                break;

            case 'int':
                $result = 'i:' . $value;
                break;

            case 'float':
                // Use toHex() because it will be unique for every possible float value, including special values.
                $result = 'f:' . Double::toHex($value);
                break;

            case 'string':
                $result = 's:' . strlen($value) . ":$value";
                break;

            case 'array':
                $result = 'a:' . count($value) . ':' . Stringify::stringifyArray($value);
                break;

            default:
                // Resources.
                if (str_starts_with($type, 'resource')) {
                    $result = 'r:' . get_resource_type($value) . ':' . get_resource_id($value);
                }
                // Objects.
                elseif (is_object($value)) {
                    $result = "o:$type:" . spl_object_id($value);
                }
                else {
                    // Not sure if this can ever actually happen. gettype() can return 'unknown type' but
                    // get_debug_type() has no equivalent. Defensive programming.
                    throw new TypeError("Key has unknown type.");
                }
                break;
        }

        return $result;
    }

    // endregion
}
