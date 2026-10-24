<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Set;

use Mossy2100\Collections\Collection;
use Mossy2100\Collections\Sequence;
use Mossy2100\Collections\Set;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Set conversion methods.
 */
#[CoversClass(Set::class)]
#[CoversClass(Collection::class)]
class SetConversionTest extends TestCase
{
    #region Method __toString() tests.

    /**
     * Test __toString returns string representation.
     */
    public function testToStringReturnsString(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $string = (string) $set;

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

        $string = (string) $set;

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

        $string = (string) $set;

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

    #endregion

    #region Method toSequence() tests.

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

    #endregion

    #region Method toArray() tests.

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

    #endregion
}
