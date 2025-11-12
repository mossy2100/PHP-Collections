<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Dictionary;

use ArgumentCountError;
use Galaxon\Collections\Dictionary;
use Galaxon\Collections\KeyValuePair;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;
use OutOfBoundsException;

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
     * Test adding with one parameter (KeyValuePair).
     */
    public function testAddWithKeyValuePair(): void
    {
        $dict = new Dictionary('string', 'int');
        $pair = new KeyValuePair('key1', 123);

        // Test adding the pair.
        $dict->add($pair);

        // Test the item was added.
        $this->assertCount(1, $dict);
        $this->assertEquals(123, $dict['key1']);
    }

    /**
     * Test adding with invalid single parameter throws TypeError.
     */
    public function testAddWithInvalidSingleParameter(): void
    {
        $dict = new Dictionary();

        // Test adding with an invalid single parameter throws TypeError.
        $this->expectException(TypeError::class);
        $dict->add('invalid');
    }

    /**
     * Test adding with three parameters throws ArgumentCountError.
     */
    public function testAddWithThreeParameters(): void
    {
        $dict = new Dictionary();

        // Test adding with three parameters throws ArgumentCountError.
        $this->expectException(ArgumentCountError::class);
        $dict->add('key', 'value', 'extra');
    }

    /**
     * Test adding with invalid key type throws TypeError.
     */
    public function testAddWithInvalidKeyType(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test adding with invalid key type throws TypeError.
        $this->expectException(TypeError::class);
        $dict->add(123, 456);
    }

    /**
     * Test adding with invalid value type throws TypeError.
     */
    public function testAddWithInvalidValueType(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test adding with invalid value type throws TypeError.
        $this->expectException(TypeError::class);
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
     * Test removeByKey with key with disallowed type throws TypeError.
     */
    public function testRemoveByKeyDisallowedType(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        // Test removing a key with an invalid type throws exception.
        $this->expectException(TypeError::class);
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
}
