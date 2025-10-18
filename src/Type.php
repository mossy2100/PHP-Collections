<?php

declare(strict_types = 1);

namespace Galaxon\Collections;

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

    // region Methods for converting values into strings

    /**
     * Convert any PHP value into a unique string.
     *
     * @param mixed $value The value to convert.
     * @return string The unique string key.
     */
    public static function getString(mixed $value): string
    {
        $type = get_debug_type($value);
        $result = '';

        // Core types.
        switch ($type) {
            case 'null':
                $result = 'null';
                break;

            case 'bool':
                $result = $value ? 'true' : 'false';
                break;

            case 'int':
                $result = (string)$value;
                break;

            case 'float':
                $result = Double::toString($value);
                break;

            case 'string':
                $result = '"' . addslashes($value) . '"';
                break;

            case 'array':
                $result = self::arrayToString($value);
                break;

            default:
                // Resources.
                if (str_starts_with($type, 'resource')) {
                    // Return the resource type and ID as a tag.
                    $resource_type = get_resource_type($value);
                    $resource_id = get_resource_id($value);
                    $result = "<resource type=\"$resource_type\" id=\"$resource_id\">";
                }
                // Objects.
                elseif (is_object($value)) {
                    // Return the class and object ID as a tag.
                    $object_id = spl_object_id($value);
                    $result = "<$type id=\"$object_id\">";
                }
                else {
                    // Not sure if this can ever actually happen. gettype() can return 'unknown type' but
                    // get_debug_type() has no equivalent.
                    throw new TypeError("Key has unknown type.");
                }
                break;
        }

        return $result;
    }

    /**
     * Get a short string representation of the given value for use in error messages, log messages, and the like.
     *
     * @param mixed $value The value to get the string representation for.
     * @param int $max_len The maximum length of the result.
     * @return string The short string representation.
     *
     */
    public static function getShortString(mixed $value, int $max_len = 20): string {
        // Get the value as a string.
        $result = self::getString($value);

        // Trim if necessary.
        if ($max_len > 4 && strlen($result) > $max_len) {
            $result = substr($result, 0, $max_len - 3) . '...';
        }

        return $result;
    }

    /**
     * Method to convert an array into a string.
     *
     * @param array $array The array to convert.
     * @return string A string representation of the array.
     * @throws ValueError If the array contains circular references.
     */
    public static function arrayToString(array $array) {
        // Detect circular references.
        try {
            json_encode($array, JSON_THROW_ON_ERROR);
        }
        catch (JsonException $e) {
            throw new ValueError(
                "An array containing circular references cannot be converted to a string. " . $e->getMessage());
        }

        // Construct the string.
        $result = '[';
        $j = 0;
        foreach ($array as $key => $value) {
            if ($j > 0) {
                $result .= ', ';
            }
            $result .= self::getString($key) . ' => ' . self::getString($value);
            $j++;
        }
        $result .= ']';
        return $result;
    }

    // endregion
}
