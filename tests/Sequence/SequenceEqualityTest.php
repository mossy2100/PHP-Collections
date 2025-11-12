<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Sequence;

use Galaxon\Collections\Dictionary;
use Galaxon\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for Sequence equality comparison.
 */
#[CoversClass(Sequence::class)]
class SequenceEqualityTest extends TestCase
{
    /**
     * Test equal sequences return true.
     */
    public function testEqualSequencesReturnTrue(): void
    {
        // Test: Compare identical sequences
        $seq1 = new Sequence(source: [1, 2, 3]);
        $seq2 = new Sequence(source: [1, 2, 3]);

        // Test: Verify equality
        $this->assertTrue($seq1->equals($seq2));
    }

    /**
     * Test empty sequences are equal.
     */
    public function testEmptySequencesAreEqual(): void
    {
        // Test: Compare two empty sequences
        $seq1 = new Sequence();
        $seq2 = new Sequence();

        // Test: Verify empty sequences are equal
        $this->assertTrue($seq1->equals($seq2));
    }

    /**
     * Test sequences with different values are not equal.
     */
    public function testSequencesWithDifferentValuesAreNotEqual(): void
    {
        // Test: Compare sequences with different values
        $seq1 = new Sequence(source: [1, 2, 3]);
        $seq2 = new Sequence(source: [1, 2, 4]);

        // Test: Verify inequality
        $this->assertFalse($seq1->equals($seq2));
    }

    /**
     * Test sequences with different lengths are not equal.
     */
    public function testSequencesWithDifferentLengthsAreNotEqual(): void
    {
        // Test: Compare sequences of different lengths
        $seq1 = new Sequence(source: [1, 2, 3]);
        $seq2 = new Sequence(source: [1, 2]);

        // Test: Verify inequality
        $this->assertFalse($seq1->equals($seq2));
    }

    /**
     * Test sequences with same values in different order are not equal.
     */
    public function testSequencesWithSameValuesInDifferentOrderAreNotEqual(): void
    {
        // Test: Compare sequences with reversed order
        $seq1 = new Sequence(source: [1, 2, 3]);
        $seq2 = new Sequence(source: [3, 2, 1]);

        // Test: Verify inequality (order matters)
        $this->assertFalse($seq1->equals($seq2));
    }

    /**
     * Test sequence not equal to different collection type.
     */
    public function testSequenceNotEqualToDifferentCollectionType(): void
    {
        // Test: Compare Sequence to Dictionary
        $seq = new Sequence(source: [1, 2, 3]);
        $dict = new Dictionary();
        $dict[0] = 1;
        $dict[1] = 2;
        $dict[2] = 3;

        // Test: Verify different collection types are not equal
        $this->assertFalse($seq->equals($dict));
    }

    /**
     * Test sequences with null values are equal.
     */
    public function testSequencesWithNullValuesAreEqual(): void
    {
        // Test: Compare sequences containing nulls
        $seq1 = new Sequence(source: [1, null, 3]);
        $seq2 = new Sequence(source: [1, null, 3]);

        // Test: Verify equality with null values
        $this->assertTrue($seq1->equals($seq2));
    }

    /**
     * Test sequences with mixed types are equal.
     */
    public function testSequencesWithMixedTypesAreEqual(): void
    {
        // Test: Compare sequences with various types
        $seq1 = new Sequence(source: [1, 'hello', 3.14, true, null]);
        $seq2 = new Sequence(source: [1, 'hello', 3.14, true, null]);

        // Test: Verify equality across different types
        $this->assertTrue($seq1->equals($seq2));
    }

    /**
     * Test sequences with objects use identity comparison.
     */
    public function testSequencesWithObjectsUseIdentityComparison(): void
    {
        // Test: Compare sequences with object references
        $obj1 = new stdClass();
        $obj2 = new stdClass();

        $seq1 = new Sequence(source: [$obj1]);
        $seq2 = new Sequence(source: [$obj1]); // Same object
        $seq3 = new Sequence(source: [$obj2]); // Different object

        // Test: Verify identity comparison (===)
        $this->assertTrue($seq1->equals($seq2));
        $this->assertFalse($seq1->equals($seq3));
    }

    /**
     * Test sequences with arrays use strict comparison.
     */
    public function testSequencesWithArraysUseStrictComparison(): void
    {
        // Test: Compare sequences containing arrays
        $seq1 = new Sequence(source: [[1, 2], [3, 4]]);
        $seq2 = new Sequence(source: [[1, 2], [3, 4]]);
        $seq3 = new Sequence(source: [[1, 2], [3, 5]]);

        // Test: Verify strict array comparison
        $this->assertTrue($seq1->equals($seq2));
        $this->assertFalse($seq1->equals($seq3));
    }

    /**
     * Test type constraints are ignored.
     */
    public function testTypeConstraintsAreIgnored(): void
    {
        // Test: Compare sequences with different type constraints
        $seq1 = new Sequence('int', 0);
        $seq2 = new Sequence(null, 0); // No type constraint

        $seq1->append(1);
        $seq1->append(2);
        $seq2->append(1);
        $seq2->append(2);

        // Test: Verify type constraints don't affect equality
        $this->assertTrue($seq1->equals($seq2));
    }

    /**
     * Test default values do not affect equality.
     */
    public function testDefaultValuesDoNotAffectEquality(): void
    {
        // Test: Compare sequences with different default values
        $seq1 = new Sequence(null, 0);  // Default value 0
        $seq2 = new Sequence(null, 99); // Default value 99

        $seq1->append(1);
        $seq1->append(2);
        $seq2->append(1);
        $seq2->append(2);

        // Test: Verify default values don't affect equality
        $this->assertTrue($seq1->equals($seq2));
    }
}
