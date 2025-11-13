<?php

declare(strict_types=1);

namespace Galaxon\Collections\Tests;

use DateTime;
use Galaxon\Collections\KeyValuePair;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for KeyValuePair class.
 */
#[CoversClass(KeyValuePair::class)]
class KeyValuePairTest extends TestCase
{
    /**
     * Test constructor with scalar key and value.
     */
    public function testConstructorWithScalars(): void
    {
        $pair = new KeyValuePair('name', 'Alice');

        $this->assertInstanceOf(KeyValuePair::class, $pair);
        $this->assertSame('name', $pair->key);
        $this->assertSame('Alice', $pair->value);
    }

    /**
     * Test constructor with integer key.
     */
    public function testConstructorWithIntegerKey(): void
    {
        $pair = new KeyValuePair(42, 'answer');

        $this->assertSame(42, $pair->key);
        $this->assertSame('answer', $pair->value);
    }

    /**
     * Test constructor with null key.
     */
    public function testConstructorWithNullKey(): void
    {
        $pair = new KeyValuePair(null, 'value');

        $this->assertNull($pair->key);
        $this->assertSame('value', $pair->value);
    }

    /**
     * Test constructor with null value.
     */
    public function testConstructorWithNullValue(): void
    {
        $pair = new KeyValuePair('key', null);

        $this->assertSame('key', $pair->key);
        $this->assertNull($pair->value);
    }

    /**
     * Test constructor with both null.
     */
    public function testConstructorWithBothNull(): void
    {
        $pair = new KeyValuePair(null, null);

        $this->assertNull($pair->key);
        $this->assertNull($pair->value);
    }

    /**
     * Test constructor with array key.
     */
    public function testConstructorWithArrayKey(): void
    {
        $key = [1, 2, 3];
        $pair = new KeyValuePair($key, 'coordinates');

        $this->assertSame($key, $pair->key);
        $this->assertSame('coordinates', $pair->value);
    }

    /**
     * Test constructor with array value.
     */
    public function testConstructorWithArrayValue(): void
    {
        $value = ['a', 'b', 'c'];
        $pair = new KeyValuePair('letters', $value);

        $this->assertSame('letters', $pair->key);
        $this->assertSame($value, $pair->value);
    }

    /**
     * Test constructor with object key.
     */
    public function testConstructorWithObjectKey(): void
    {
        $key = new DateTime('2024-01-01');
        $pair = new KeyValuePair($key, 'event');

        $this->assertSame($key, $pair->key);
        $this->assertSame('event', $pair->value);
    }

    /**
     * Test constructor with object value.
     */
    public function testConstructorWithObjectValue(): void
    {
        $value = new DateTime('2024-01-01');
        $pair = new KeyValuePair('date', $value);

        $this->assertSame('date', $pair->key);
        $this->assertSame($value, $pair->value);
    }

    /**
     * Test constructor with boolean key.
     */
    public function testConstructorWithBooleanKey(): void
    {
        $pair = new KeyValuePair(true, 'yes');

        $this->assertTrue($pair->key);
        $this->assertSame('yes', $pair->value);
    }

    /**
     * Test constructor with float key.
     */
    public function testConstructorWithFloatKey(): void
    {
        $pair = new KeyValuePair(3.14, 'pi');

        $this->assertSame(3.14, $pair->key);
        $this->assertSame('pi', $pair->value);
    }

    /**
     * Test constructor with resource key.
     */
    public function testConstructorWithResourceKey(): void
    {
        $resource = fopen('php://memory', 'r');
        $pair = new KeyValuePair($resource, 'stream');

        $this->assertSame($resource, $pair->key);
        $this->assertSame('stream', $pair->value);

        fclose($resource);
    }

    /**
     * Test that KeyValuePair properties are accessible.
     */
    public function testPropertiesAreAccessible(): void
    {
        $pair = new KeyValuePair('key', 'value');

        // Verify properties are accessible
        $this->assertSame('key', $pair->key);
        $this->assertSame('value', $pair->value);

        // Note: Properties are readonly, but PHP will enforce this at runtime
        // if modification is attempted, causing an error
    }

    /**
     * Test multiple KeyValuePairs are independent.
     */
    public function testMultiplePairsAreIndependent(): void
    {
        $pair1 = new KeyValuePair('key1', 'value1');
        $pair2 = new KeyValuePair('key2', 'value2');

        $this->assertSame('key1', $pair1->key);
        $this->assertSame('value1', $pair1->value);
        $this->assertSame('key2', $pair2->key);
        $this->assertSame('value2', $pair2->value);
    }

    /**
     * Test with mixed types for key and value.
     */
    public function testConstructorWithMixedTypes(): void
    {
        $pair1 = new KeyValuePair(1, 'string');
        $pair2 = new KeyValuePair('string', 2);
        $pair3 = new KeyValuePair([1, 2], new DateTime());
        $pair4 = new KeyValuePair(true, false);

        $this->assertSame(1, $pair1->key);
        $this->assertSame('string', $pair1->value);
        $this->assertSame('string', $pair2->key);
        $this->assertSame(2, $pair2->value);
        $this->assertIsArray($pair3->key);
        $this->assertInstanceOf(DateTime::class, $pair3->value);
        $this->assertTrue($pair4->key);
        $this->assertFalse($pair4->value);
    }
}
