<?php

declare(strict_types=1);

namespace Galaxon\Collections\Tests\Dictionary;

use Galaxon\Collections\Dictionary;
use Galaxon\Collections\KeyValuePair;
use Galaxon\Collections\Sequence;
use Galaxon\Collections\Set;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Dictionary conversion methods.
 */
#[CoversClass(Dictionary::class)]
class DictionaryConversionTest extends TestCase
{
    /**
     * Test toArray converts Dictionary to array of KeyValuePair objects.
     */
    public function testToArrayConvertsToArray(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test converting to array.
        $array = $dict->toArray();

        // Test array structure (contains KeyValuePair objects as internal representation).
        $this->assertIsArray($array);
        $this->assertCount(3, $array);
        $this->assertInstanceOf(KeyValuePair::class, $array[0]);
        $this->assertInstanceOf(KeyValuePair::class, $array[1]);
        $this->assertInstanceOf(KeyValuePair::class, $array[2]);
    }

    /**
     * Test toArray on empty dictionary.
     */
    public function testToArrayOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test converting empty dictionary to array.
        $array = $dict->toArray();

        // Test empty array is returned.
        $this->assertIsArray($array);
        $this->assertCount(0, $array);
        $this->assertEquals([], $array);
    }

    /**
     * Test toArray returns independent copy.
     */
    public function testToArrayReturnsIndependentCopy(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        // Test modifying array doesn't affect dictionary.
        $array = $dict->toArray();
        $array = [];

        // Test dictionary is unchanged.
        $this->assertCount(1, $dict);
        $this->assertEquals(1, $dict['a']);
    }

    /**
     * Test toArray preserves order.
     */
    public function testToArrayPreservesOrder(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('first', 1);
        $dict->add('second', 2);
        $dict->add('third', 3);

        // Test array entries are in insertion order.
        /** @var KeyValuePair[] $array */
        $array = $dict->toArray();

        $this->assertEquals('first', $array[0]->key);
        $this->assertEquals('second', $array[1]->key);
        $this->assertEquals('third', $array[2]->key);
    }

    /**
     * Test toArray returns array with numeric indexes.
     */
    public function testToArrayReturnsNumericIndexes(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test array uses numeric indexes, not dictionary keys.
        $array = $dict->toArray();

        // Test array has sequential numeric keys.
        $this->assertArrayHasKey(0, $array);
        $this->assertArrayHasKey(1, $array);
        $this->assertArrayNotHasKey('a', $array);
        $this->assertArrayNotHasKey('b', $array);
    }

    /**
     * Test toSequence converts dictionary to Sequence.
     */
    public function testToSequenceConvertsToSequence(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test converting to Sequence.
        $sequence = $dict->toSequence();

        // Test Sequence contains KeyValuePairs.
        $this->assertInstanceOf(Sequence::class, $sequence);
        $this->assertCount(3, $sequence);
        $this->assertInstanceOf(KeyValuePair::class, $sequence[0]);
        $this->assertInstanceOf(KeyValuePair::class, $sequence[1]);
        $this->assertInstanceOf(KeyValuePair::class, $sequence[2]);
    }

    /**
     * Test toSequence preserves order.
     */
    public function testToSequencePreservesOrder(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('first', 1);
        $dict->add('second', 2);
        $dict->add('third', 3);

        // Test converting to Sequence.
        $sequence = $dict->toSequence();

        // Test order is preserved.
        /** @var KeyValuePair $pair */
        $pair = $sequence[0];
        $this->assertEquals('first', $pair->key);

        /** @var KeyValuePair $pair */
        $pair = $sequence[1];
        $this->assertEquals('second', $pair->key);

        /** @var KeyValuePair $pair */
        $pair = $sequence[2];
        $this->assertEquals('third', $pair->key);
    }

    /**
     * Test toSequence on empty dictionary.
     */
    public function testToSequenceOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test converting empty dictionary to Sequence.
        $sequence = $dict->toSequence();

        // Test empty Sequence is returned.
        $this->assertInstanceOf(Sequence::class, $sequence);
        $this->assertCount(0, $sequence);
    }

    /**
     * Test toSequence creates independent copy.
     */
    public function testToSequenceCreatesIndependentCopy(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        // Test Sequence and Dictionary are independent.
        $sequence = $dict->toSequence();
        $dict->add('b', 2);

        // Test Sequence hasn't changed.
        $this->assertCount(1, $sequence);
        $this->assertCount(2, $dict);
    }

    /**
     * Test __toString returns string representation.
     */
    public function testToStringReturnsString(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test converting to string.
        $string = (string)$dict;

        // Test a string is returned.
        $this->assertIsString($string);
        $this->assertNotEmpty($string);
    }

    /**
     * Test __toString on empty dictionary.
     */
    public function testToStringOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test converting empty dictionary to string.
        $string = (string)$dict;

        // Test a string is returned.
        $this->assertIsString($string);
    }

    /**
     * Test __toString can be implicitly called.
     */
    public function testToStringImplicitCall(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('key', 123);

        // Test implicit string conversion.
        $output = "Dictionary: $dict";

        // Test string contains expected prefix.
        $this->assertStringContainsString('Dictionary:', $output);
    }

    /**
     * Test iterator_to_array creates associative array.
     */
    public function testIteratorToArrayCreatesAssociativeArray(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test converting iterator to array.
        $array = iterator_to_array($dict);

        // Test associative array is created.
        $this->assertIsArray($array);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $array);
    }

    /**
     * Test count interface works correctly.
     */
    public function testCountInterface(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test count on empty dictionary.
        $this->assertCount(0, $dict);

        // Test count after adding items.
        $dict->add('a', 1);
        $this->assertCount(1, $dict);

        $dict->add('b', 2);
        $this->assertCount(2, $dict);

        // Test count after removing items.
        $dict->removeByKey('a');
        $this->assertCount(1, $dict);
    }
}
