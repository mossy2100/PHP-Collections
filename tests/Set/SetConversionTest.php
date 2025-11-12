<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Set;

use Galaxon\Collections\Dictionary;
use Galaxon\Collections\Sequence;
use Galaxon\Collections\Set;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Set conversion methods.
 */
#[CoversClass(Set::class)]
class SetConversionTest extends TestCase
{
    /**
     * Test toArray converts Set to array.
     */
    public function testToArrayConvertsToArray(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $array = $set->toArray();

        $this->assertIsArray($array);
        $this->assertCount(3, $array);
        $this->assertContains(1, $array);
        $this->assertContains(2, $array);
        $this->assertContains(3, $array);
    }

    /**
     * Test toArray on empty set.
     */
    public function testToArrayOnEmptySet(): void
    {
        $set = new Set('int');

        $array = $set->toArray();

        $this->assertIsArray($array);
        $this->assertCount(0, $array);
        $this->assertEquals([], $array);
    }

    /**
     * Test toArray returns independent copy.
     */
    public function testToArrayReturnsIndependentCopy(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $array = $set->toArray();
        $array[] = 4;

        // Test set is unchanged.
        $this->assertCount(3, $set);
        $this->assertFalse($set->contains(4));
    }

    /**
     * Test toDictionary converts set to dictionary.
     */
    public function testToDictionaryConvertsToDictionary(): void
    {
        $set = new Set('string');
        $set->add('apple', 'banana', 'cherry');

        $dict = $set->toDictionary();

        $this->assertInstanceOf(Dictionary::class, $dict);
        $this->assertCount(3, $dict);
    }

    /**
     * Test toDictionary creates sequential keys.
     */
    public function testToDictionaryCreatesSequentialKeys(): void
    {
        $set = new Set('string');
        $set->add('a', 'b', 'c');

        $dict = $set->toDictionary();

        // Test keys are sequential unsigned integers.
        $this->assertTrue($dict->keyExists(0));
        $this->assertTrue($dict->keyExists(1));
        $this->assertTrue($dict->keyExists(2));

        // Test values are preserved.
        $values = $dict->values();
        $this->assertContains('a', $values);
        $this->assertContains('b', $values);
        $this->assertContains('c', $values);
    }

    /**
     * Test toDictionary on empty set.
     */
    public function testToDictionaryOnEmptySet(): void
    {
        $set = new Set('int');

        $dict = $set->toDictionary();

        $this->assertInstanceOf(Dictionary::class, $dict);
        $this->assertCount(0, $dict);
        $this->assertTrue($dict->empty());
    }

    /**
     * Test toDictionary creates independent copy.
     */
    public function testToDictionaryCreatesIndependentCopy(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $dict = $set->toDictionary();
        $set->add(4);

        // Test dictionary hasn't changed.
        $this->assertCount(3, $dict);
        $this->assertCount(4, $set);
    }

    /**
     * Test toDictionary preserves value types.
     */
    public function testToDictionaryPreservesValueTypes(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $dict = $set->toDictionary();

        $this->assertTrue($dict->valueTypes->containsOnly('int'));
    }

    /**
     * Test toSequence converts set to sequence.
     */
    public function testToSequenceConvertsToSequence(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $seq = $set->toSequence();

        $this->assertInstanceOf(Sequence::class, $seq);
        $this->assertCount(3, $seq);
    }

    /**
     * Test toSequence preserves all values.
     */
    public function testToSequencePreservesValues(): void
    {
        $set = new Set('int');
        $set->add(10, 20, 30);

        $seq = $set->toSequence();

        // Test all values are present in sequence.
        $this->assertTrue($seq->contains(10));
        $this->assertTrue($seq->contains(20));
        $this->assertTrue($seq->contains(30));
    }

    /**
     * Test toSequence on empty set.
     */
    public function testToSequenceOnEmptySet(): void
    {
        $set = new Set('int');

        $seq = $set->toSequence();

        $this->assertInstanceOf(Sequence::class, $seq);
        $this->assertCount(0, $seq);
        $this->assertTrue($seq->empty());
    }

    /**
     * Test toSequence creates independent copy.
     */
    public function testToSequenceCreatesIndependentCopy(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $seq = $set->toSequence();
        $set->add(4);

        // Test sequence hasn't changed.
        $this->assertCount(3, $seq);
        $this->assertCount(4, $set);
    }

    /**
     * Test toSequence preserves value types.
     */
    public function testToSequencePreservesValueTypes(): void
    {
        $set = new Set('string');
        $set->add('a', 'b', 'c');

        $seq = $set->toSequence();

        $this->assertTrue($seq->valueTypes->containsOnly('string'));
    }

    /**
     * Test __toString returns string representation.
     */
    public function testToStringReturnsString(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $string = (string)$set;

        $this->assertIsString($string);
        $this->assertNotEmpty($string);
        $this->assertStringContainsString('1', $string);
        $this->assertStringContainsString('2', $string);
        $this->assertStringContainsString('3', $string);
    }

    /**
     * Test __toString on empty set.
     */
    public function testToStringOnEmptySet(): void
    {
        $set = new Set('int');

        $string = (string)$set;

        $this->assertIsString($string);
        $this->assertEquals('{}', $string);
    }

    /**
     * Test __toString uses set notation.
     */
    public function testToStringUsesSetNotation(): void
    {
        $set = new Set('int');
        $set->add(1);

        $string = (string)$set;

        $this->assertStringStartsWith('{', $string);
        $this->assertStringEndsWith('}', $string);
    }

    /**
     * Test __toString can be implicitly called.
     */
    public function testToStringImplicitCall(): void
    {
        $set = new Set('int');
        $set->add(42);

        $output = "Set: $set";

        $this->assertStringContainsString('Set:', $output);
        $this->assertStringContainsString('42', $output);
    }

    /**
     * Test iterator_to_array creates array with sequential keys.
     */
    public function testIteratorToArrayCreatesSequentialArray(): void
    {
        $set = new Set('string');
        $set->add('a', 'b', 'c');

        $array = iterator_to_array($set);

        $this->assertIsArray($array);
        $this->assertCount(3, $array);
        $this->assertArrayHasKey(0, $array);
        $this->assertArrayHasKey(1, $array);
        $this->assertArrayHasKey(2, $array);
    }

    /**
     * Test count interface works correctly.
     */
    public function testCountInterface(): void
    {
        $set = new Set('int');

        // Test count on empty set.
        $this->assertCount(0, $set);

        // Test count after adding items.
        $set->add(1);
        $this->assertCount(1, $set);

        $set->add(2, 3);
        $this->assertCount(3, $set);

        // Test count after removing items.
        $set->remove(1);
        $this->assertCount(2, $set);
    }

    /**
     * Test foreach iteration.
     */
    public function testForeachIteration(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $values = [];
        foreach ($set as $value) {
            $values[] = $value;
        }

        $this->assertCount(3, $values);
        $this->assertContains(1, $values);
        $this->assertContains(2, $values);
        $this->assertContains(3, $values);
    }

    /**
     * Test foreach iteration with keys.
     */
    public function testForeachIterationWithKeys(): void
    {
        $set = new Set('string');
        $set->add('a', 'b', 'c');

        $keys = [];
        $values = [];
        foreach ($set as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        // Test sequential numeric keys are generated.
        $this->assertEquals([0, 1, 2], $keys);
        $this->assertCount(3, $values);
    }
}
