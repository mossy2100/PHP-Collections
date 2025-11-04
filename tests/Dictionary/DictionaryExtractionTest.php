<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Dictionary;

use Galaxon\Collections\Dictionary;
use Galaxon\Collections\KeyValuePair;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Dictionary extraction methods (keys, values, entries).
 */
#[CoversClass(Dictionary::class)]
class DictionaryExtractionTest extends TestCase
{
    /**
     * Test keys returns all keys as an array.
     */
    public function testKeysReturnsAllKeys(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test getting keys.
        $keys = $dict->keys();

        // Test correct keys were returned.
        $this->assertIsArray($keys);
        $this->assertCount(3, $keys);
        $this->assertEquals(['a', 'b', 'c'], $keys);
    }

    /**
     * Test keys on empty dictionary returns empty array.
     */
    public function testKeysOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test getting keys from empty dictionary.
        $keys = $dict->keys();

        // Test empty array is returned.
        $this->assertIsArray($keys);
        $this->assertCount(0, $keys);
        $this->assertEquals([], $keys);
    }

    /**
     * Test keys with various key types.
     */
    public function testKeysWithVariousKeyTypes(): void
    {
        $dict = new Dictionary();
        $dict->add(1, 'one');
        $dict->add('two', 2);
        $dict->add(3.5, 'three-point-five');

        // Test getting keys of various types.
        $keys = $dict->keys();

        // Test correct keys were returned.
        $this->assertCount(3, $keys);
        $this->assertContains(1, $keys);
        $this->assertContains('two', $keys);
        $this->assertContains(3.5, $keys);
    }

    /**
     * Test keys preserves order.
     */
    public function testKeysPreservesOrder(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('first', 1);
        $dict->add('second', 2);
        $dict->add('third', 3);

        // Test keys are in insertion order.
        $keys = $dict->keys();

        $this->assertEquals(['first', 'second', 'third'], $keys);
    }

    /**
     * Test values returns all values as an array.
     */
    public function testValuesReturnsAllValues(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test getting values.
        $values = $dict->values();

        // Test correct values were returned.
        $this->assertIsArray($values);
        $this->assertCount(3, $values);
        $this->assertEquals([1, 2, 3], $values);
    }

    /**
     * Test values on empty dictionary returns empty array.
     */
    public function testValuesOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test getting values from empty dictionary.
        $values = $dict->values();

        // Test empty array is returned.
        $this->assertIsArray($values);
        $this->assertCount(0, $values);
        $this->assertEquals([], $values);
    }

    /**
     * Test values with duplicate values.
     */
    public function testValuesWithDuplicateValues(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 1);

        // Test getting values includes duplicates.
        $values = $dict->values();

        // Test duplicate values are preserved.
        $this->assertCount(3, $values);
        $this->assertEquals([1, 2, 1], $values);
    }

    /**
     * Test values preserves order.
     */
    public function testValuesPreservesOrder(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 10);
        $dict->add('b', 20);
        $dict->add('c', 30);

        // Test values are in insertion order.
        $values = $dict->values();

        $this->assertEquals([10, 20, 30], $values);
    }

    /**
     * Test entries returns all key-value pairs as an array.
     */
    public function testEntriesReturnsAllPairs(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test getting entries.
        $entries = $dict->entries();

        // Test correct entries were returned.
        $this->assertIsArray($entries);
        $this->assertCount(2, $entries);

        // Test each entry is a KeyValuePair.
        $this->assertInstanceOf(KeyValuePair::class, $entries[0]);
        $this->assertInstanceOf(KeyValuePair::class, $entries[1]);

        // Test correct keys and values.
        $this->assertEquals('a', $entries[0]->key);
        $this->assertEquals(1, $entries[0]->value);
        $this->assertEquals('b', $entries[1]->key);
        $this->assertEquals(2, $entries[1]->value);
    }

    /**
     * Test entries on empty dictionary returns empty array.
     */
    public function testEntriesOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test getting entries from empty dictionary.
        $entries = $dict->entries();

        // Test empty array is returned.
        $this->assertIsArray($entries);
        $this->assertCount(0, $entries);
        $this->assertEquals([], $entries);
    }

    /**
     * Test entries preserves order.
     */
    public function testEntriesPreservesOrder(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('first', 1);
        $dict->add('second', 2);
        $dict->add('third', 3);

        // Test entries are in insertion order.
        $entries = $dict->entries();

        $this->assertEquals('first', $entries[0]->key);
        $this->assertEquals('second', $entries[1]->key);
        $this->assertEquals('third', $entries[2]->key);
    }

    /**
     * Test entries returns array with numeric indexes.
     */
    public function testEntriesReturnsNumericIndexes(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test entries uses numeric indexes.
        $entries = $dict->entries();

        // Test array has sequential numeric keys.
        $this->assertArrayHasKey(0, $entries);
        $this->assertArrayHasKey(1, $entries);
        $this->assertArrayNotHasKey('a', $entries);
        $this->assertArrayNotHasKey('b', $entries);
    }

    /**
     * Test modifying returned arrays doesn't affect dictionary.
     */
    public function testReturnedArraysAreIndependent(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test modifying keys array doesn't affect dictionary.
        $keys = $dict->keys();
        $keys[] = 'c';
        $this->assertCount(2, $dict);
        $this->assertFalse($dict->keyExists('c'));

        // Test modifying values array doesn't affect dictionary.
        $values = $dict->values();
        $values[] = 3;
        $this->assertCount(2, $dict);
        $this->assertFalse($dict->contains(3));

        // Test modifying entries array doesn't affect dictionary.
        $entries = $dict->entries();
        array_pop($entries);
        $this->assertCount(2, $dict);
    }
}
