<?php

declare(strict_types=1);

namespace OceanMoon\Collections\Tests;

use DateTime;
use OceanMoon\Collections\Pair;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Pair class.
 */
#[CoversClass(Pair::class)]
class PairTest extends TestCase
{
    /**
     * Test constructor with scalar key and value.
     */
    public function testConstructorWithScalars(): void
    {
        $pair = new Pair('name', 'Alice');

        $this->assertInstanceOf(Pair::class, $pair);
        $this->assertSame('name', $pair->key);
        $this->assertSame('Alice', $pair->value);
    }

    /**
     * Test constructor with integer key.
     */
    public function testConstructorWithIntegerKey(): void
    {
        $pair = new Pair(42, 'answer');

        $this->assertSame(42, $pair->key);
        $this->assertSame('answer', $pair->value);
    }

    /**
     * Test constructor with null key.
     */
    public function testConstructorWithNullKey(): void
    {
        $pair = new Pair(null, 'value');

        $this->assertNull($pair->key);
        $this->assertSame('value', $pair->value);
    }

    /**
     * Test constructor with null value.
     */
    public function testConstructorWithNullValue(): void
    {
        $pair = new Pair('key', null);

        $this->assertSame('key', $pair->key);
        $this->assertNull($pair->value);
    }

    /**
     * Test constructor with both null.
     */
    public function testConstructorWithBothNull(): void
    {
        $pair = new Pair(null, null);

        $this->assertNull($pair->key);
        $this->assertNull($pair->value);
    }

    /**
     * Test constructor with array key.
     */
    public function testConstructorWithArrayKey(): void
    {
        $key = [1, 2, 3];
        $pair = new Pair($key, 'coordinates');

        $this->assertSame($key, $pair->key);
        $this->assertSame('coordinates', $pair->value);
    }

    /**
     * Test constructor with array value.
     */
    public function testConstructorWithArrayValue(): void
    {
        $value = ['a', 'b', 'c'];
        $pair = new Pair('letters', $value);

        $this->assertSame('letters', $pair->key);
        $this->assertSame($value, $pair->value);
    }

    /**
     * Test constructor with object key.
     */
    public function testConstructorWithObjectKey(): void
    {
        $key = new DateTime('2024-01-01');
        $pair = new Pair($key, 'event');

        $this->assertSame($key, $pair->key);
        $this->assertSame('event', $pair->value);
    }

    /**
     * Test constructor with object value.
     */
    public function testConstructorWithObjectValue(): void
    {
        $value = new DateTime('2024-01-01');
        $pair = new Pair('date', $value);

        $this->assertSame('date', $pair->key);
        $this->assertSame($value, $pair->value);
    }

    /**
     * Test constructor with boolean key.
     */
    public function testConstructorWithBooleanKey(): void
    {
        $pair = new Pair(true, 'yes');

        $this->assertTrue($pair->key);
        $this->assertSame('yes', $pair->value);
    }

    /**
     * Test constructor with float key.
     */
    public function testConstructorWithFloatKey(): void
    {
        $pair = new Pair(3.14, 'pi');

        $this->assertSame(3.14, $pair->key);
        $this->assertSame('pi', $pair->value);
    }

    /**
     * Test constructor with resource key.
     */
    public function testConstructorWithResourceKey(): void
    {
        $resource = fopen('php://memory', 'r');
        $this->assertIsResource($resource);

        $pair = new Pair($resource, 'stream');

        $this->assertSame($resource, $pair->key);
        $this->assertSame('stream', $pair->value);

        fclose($resource);
    }

    /**
     * Test that Pair properties are accessible.
     */
    public function testPropertiesAreAccessible(): void
    {
        $pair = new Pair('key', 'value');

        // Verify properties are accessible
        $this->assertSame('key', $pair->key);
        $this->assertSame('value', $pair->value);

        // Note: Properties are readonly, but PHP will enforce this at runtime
        // if modification is attempted, causing an error
    }

    /**
     * Test multiple Pairs are independent.
     */
    public function testMultiplePairsAreIndependent(): void
    {
        $pair1 = new Pair('key1', 'value1');
        $pair2 = new Pair('key2', 'value2');

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
        $pair1 = new Pair(1, 'string');
        $pair2 = new Pair('string', 2);
        $pair3 = new Pair([1, 2], new DateTime());
        $pair4 = new Pair(true, false);

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
