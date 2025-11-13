<?php

declare(strict_types=1);

namespace Galaxon\Collections\Tests\Dictionary;

use Galaxon\Collections\Dictionary;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Tests for Dictionary ArrayAccess implementation.
 */
#[CoversClass(Dictionary::class)]
class DictionaryArrayAccessTest extends TestCase
{
    /**
     * Test offsetSet with valid key and value.
     */
    public function testOffsetSetValidKeyValue(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test setting a value using array syntax.
        $dict['key1'] = 123;

        // Test the value was set.
        $this->assertCount(1, $dict);
        $this->assertEquals(123, $dict['key1']);
    }

    /**
     * Test offsetSet replaces existing value for duplicate key.
     */
    public function testOffsetSetReplacesValue(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict['key'] = 123;

        // Test replacing the value.
        $dict['key'] = 456;

        // Test only one item exists with the new value.
        $this->assertCount(1, $dict);
        $this->assertEquals(456, $dict['key']);
    }

    /**
     * Test offsetSet with null key when null is allowed.
     */
    public function testOffsetSetWithNullKeyAllowed(): void
    {
        $dict = new Dictionary('?string', 'int');

        // Test setting value with null key using array append syntax.
        $dict[] = 123;

        // Test the value was set with null key.
        $this->assertCount(1, $dict);
        $this->assertEquals(123, $dict[null]);
    }

    /**
     * Test offsetSet with null key when null is not allowed throws TypeError.
     */
    public function testOffsetSetWithNullKeyNotAllowed(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test setting with null key throws TypeError.
        $this->expectException(TypeError::class);
        $dict[] = 123;
    }

    /**
     * Test offsetSet with invalid key type throws TypeError.
     */
    public function testOffsetSetInvalidKeyType(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test setting with invalid key type throws TypeError.
        $this->expectException(TypeError::class);
        $dict[123] = 456;
    }

    /**
     * Test offsetSet with invalid value type throws TypeError.
     */
    public function testOffsetSetInvalidValueType(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test setting with invalid value type throws TypeError.
        $this->expectException(TypeError::class);
        $dict['key'] = 'not an int';
    }

    /**
     * Test offsetGet retrieves correct value.
     */
    public function testOffsetGetRetrievesValue(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict['key'] = 123;

        // Test getting the value.
        $value = $dict['key'];

        // Test the correct value was retrieved.
        $this->assertEquals(123, $value);
    }

    /**
     * Test offsetGet with non-existent key throws OutOfBoundsException.
     */
    public function testOffsetGetNonExistentKey(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test getting non-existent key throws OutOfBoundsException.
        $this->expectException(OutOfBoundsException::class);
        $value = $dict['nonexistent'];
    }

    /**
     * Test offsetExists returns true for existing key.
     */
    public function testOffsetExistsWithExistingKey(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict['key'] = 123;

        // Test checking for existing key.
        $exists = isset($dict['key']);

        // Test it returns true.
        $this->assertTrue($exists);
    }

    /**
     * Test offsetExists returns false for non-existent key.
     */
    public function testOffsetExistsWithNonExistentKey(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test checking for non-existent key.
        $exists = isset($dict['nonexistent']);

        // Test it returns false.
        $this->assertFalse($exists);
    }

    /**
     * Test offsetExists with null key.
     */
    public function testOffsetExistsWithNullKey(): void
    {
        $dict = new Dictionary('?string', 'int');
        $dict[null] = 123;

        // Test checking for null key.
        $exists = isset($dict[null]);

        // Test it returns true.
        $this->assertTrue($exists);
    }

    /**
     * Test offsetUnset removes an item.
     */
    public function testOffsetUnsetRemovesItem(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict['a'] = 1;
        $dict['b'] = 2;

        // Test unsetting an item.
        unset($dict['a']);

        // Test the item was removed.
        $this->assertCount(1, $dict);
        $this->assertFalse(isset($dict['a']));
        $this->assertEquals(2, $dict['b']);
    }

    /**
     * Test offsetUnset with non-existent key throws OutOfBoundsException.
     */
    public function testOffsetUnsetNonExistentKey(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test unsetting non-existent key throws OutOfBoundsException.
        $this->expectException(OutOfBoundsException::class);
        unset($dict['nonexistent']);
    }

    /**
     * Test using Dictionary with various key types.
     */
    public function testArrayAccessWithVariousKeyTypes(): void
    {
        $dict = new Dictionary();

        // Test with int key.
        $dict[1] = 'int key';
        $this->assertEquals('int key', $dict[1]);

        // Test with string key.
        $dict['string'] = 'string key';
        $this->assertEquals('string key', $dict['string']);

        // Test with float key.
        $dict[3.14] = 'float key';
        $this->assertEquals('float key', $dict[3.14]);

        // Test with bool key.
        $dict[true] = 'bool key';
        $this->assertEquals('bool key', $dict[true]);

        // Test all items exist.
        $this->assertCount(4, $dict);
    }

    /**
     * Test multiple null key assignments keep only the last value.
     */
    public function testMultipleNullKeyAssignments(): void
    {
        $dict = new Dictionary('?string', 'int');

        // Test multiple assignments with null key.
        $dict[] = 1;
        $dict[] = 2;
        $dict[] = 3;

        // Test only one item exists with the last value.
        $this->assertCount(1, $dict);
        $this->assertEquals(3, $dict[null]);
    }
}
