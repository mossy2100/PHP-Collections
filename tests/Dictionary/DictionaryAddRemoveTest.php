<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Dictionary;

use BadMethodCallException;
use InvalidArgumentException;
use Mossy2100\Collections\Dictionary;
use Mossy2100\Collections\Pair;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Dictionary add and remove methods.
 */
#[CoversClass(Dictionary::class)]
class DictionaryAddRemoveTest extends TestCase
{
    /**
     * Test adding with two parameters (key and value).
     */
    public function testAddWithTwoParameters(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test adding a pair.
        $result = $dict->add('key1', 123);

        // Test fluent interface.
        $this->assertSame($dict, $result);

        // Test the item was added.
        $this->assertCount(1, $dict);
        $this->assertEquals(123, $dict['key1']);
    }

    /**
     * Test adding with one parameter (Pair).
     */
    public function testAddWithPair(): void
    {
        $dict = new Dictionary('string', 'int');
        $pair = new Pair('key1', 123);

        // Test adding the pair.
        $dict->add($pair);

        // Test the item was added.
        $this->assertCount(1, $dict);
        $this->assertEquals(123, $dict['key1']);
    }

    /**
     * Test adding with invalid single parameter throws InvalidArgumentException.
     */
    public function testAddWithInvalidSingleParameter(): void
    {
        $dict = new Dictionary();

        // Test adding with an invalid single parameter throws InvalidArgumentException.
        $this->expectException(InvalidArgumentException::class);
        $dict->add('invalid');
    }

    /**
     * Test adding with three parameters throws BadMethodCallException.
     */
    public function testAddWithThreeParameters(): void
    {
        $dict = new Dictionary();

        // Test adding with three parameters throws BadMethodCallException.
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Invalid argument count: 3. Must be 1 or 2.');
        $dict->add('key', 'value', 'extra');
    }

    /**
     * Test adding with invalid key type throws InvalidArgumentException.
     */
    public function testAddWithInvalidKeyType(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test adding with invalid key type throws InvalidArgumentException.
        $this->expectException(InvalidArgumentException::class);
        $dict->add(123, 456);
    }

    /**
     * Test adding with invalid value type throws InvalidArgumentException.
     */
    public function testAddWithInvalidValueType(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test adding with invalid value type throws InvalidArgumentException.
        $this->expectException(InvalidArgumentException::class);
        $dict->add('key', 'not an int');
    }

    /**
     * Test adding multiple items in a chain.
     */
    public function testAddChaining(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test chaining add calls.
        $dict->add('a', 1)
            ->add('b', 2)
            ->add('c', 3);

        // Test all items were added.
        $this->assertCount(3, $dict);
        $this->assertEquals(1, $dict['a']);
        $this->assertEquals(2, $dict['b']);
        $this->assertEquals(3, $dict['c']);
    }

    /**
     * Test adding a duplicate key replaces the existing value.
     */
    public function testAddDuplicateKeyReplacesValue(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('key', 123);

        // Test replacing the value.
        $dict->add('key', 456);

        // Test only one item exists with the new value.
        $this->assertCount(1, $dict);
        $this->assertEquals(456, $dict['key']);
    }

    /**
     * Test removeByKey removes an existing item.
     */
    public function testRemoveByKeyRemovesItem(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test removing an item.
        $result = $dict->removeByKey('a');

        // Test the item was removed.
        $this->assertEquals(1, $result);
        $this->assertCount(1, $dict);
        $this->assertFalse($dict->keyExists('a'));
        $this->assertEquals(2, $dict['b']);
    }

    /**
     * Test removeByKey with non-existent key throws OutOfBoundsException.
     */
    public function testRemoveByKeyNonExistentKey(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        // Test removing a non-existent key throws exception.
        $this->expectException(OutOfBoundsException::class);
        $dict->removeByKey('nonexistent');
    }

    /**
     * Test removeByKey with key with disallowed type throws InvalidArgumentException.
     */
    public function testRemoveByKeyDisallowedType(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        // Test removing a key with an invalid type throws exception.
        $this->expectException(InvalidArgumentException::class);
        $dict->removeByKey(3.14);
    }

    /**
     * Test removeByValue removes items with matching value.
     */
    public function testRemoveByValueRemovesMatchingItems(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 1);

        // Test removing by value.
        $result = $dict->removeByValue(1);

        // Test both items with value 1 were removed.
        $this->assertEquals(2, $result);
        $this->assertCount(1, $dict);
        $this->assertFalse($dict->keyExists('a'));
        $this->assertFalse($dict->keyExists('c'));
        $this->assertEquals(2, $dict['b']);
    }

    /**
     * Test removeByValue with non-existent value does nothing.
     */
    public function testRemoveByValueNonExistentValue(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test removing a non-existent value doesn't throw.
        $dict->removeByValue(999);

        // Test the dictionary is unchanged.
        $this->assertCount(2, $dict);
    }

    /**
     * Test removeByValue with a value of a disallowed type returns 0.
     */
    public function testRemoveByValueWithDisallowedType(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        // Test no exception is thrown and nothing is removed.
        $this->assertSame(0, $dict->removeByValue('1'));
        $this->assertCount(1, $dict);
    }

    /**
     * Test clear removes all items.
     */
    public function testClearRemovesAllItems(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test clearing the dictionary.
        $result = $dict->clear();

        // Test fluent interface.
        $this->assertSame($dict, $result);

        // Test all items were removed.
        $this->assertCount(0, $dict);
        $this->assertTrue($dict->empty());
    }

    /**
     * Test clear on empty dictionary.
     */
    public function testClearOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test clearing an empty dictionary doesn't throw.
        $dict->clear();

        // Test the dictionary is still empty.
        $this->assertCount(0, $dict);
        $this->assertTrue($dict->empty());
    }

    /**
     * Test removeByValue removes NAN values.
     */
    public function testRemoveByValueNan(): void
    {
        $dict = new Dictionary('string', 'float');
        $dict['a'] = NAN;
        $dict['b'] = 1.0;
        $dict['c'] = NAN;

        $this->assertSame(2, $dict->removeByValue(NAN));
        $this->assertSame(['b'], $dict->keys);
    }

    /**
     * Test removeByValue distinguishes -0.0 from 0.0.
     */
    public function testRemoveByValueNegativeZero(): void
    {
        $dict = new Dictionary('string', 'float');
        $dict['a'] = 0.0;
        $dict['b'] = -0.0;

        $this->assertSame(1, $dict->removeByValue(-0.0));
        $this->assertSame(['a'], $dict->keys);
    }

    /**
     * Test the message when the one-argument form isn't given a Pair.
     */
    public function testAddNonPairMessage(): void
    {
        $dict = new Dictionary();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument type: int. Must be Pair.');
        $dict->add(5);
    }

    /**
     * Test the message for a key of a disallowed type.
     */
    public function testKeyTypeMessage(): void
    {
        $dict = new Dictionary('string', 'int');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid key type: float. Must be string.');
        $dict->add(1.5, 1);
    }
}
