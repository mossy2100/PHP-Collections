<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Sequence;

use Mossy2100\Collections\Collection;
use Mossy2100\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Sequence inspection methods.
 */
#[CoversClass(Sequence::class)]
#[CoversClass(Collection::class)]
class SequenceInspectionTest extends TestCase
{
    #region Method contains() tests.

    /**
     * Test contains method finds existing value.
     */
    public function testContainsFindsExistingValue(): void
    {
        // Test: Check for value that exists
        $seq = new Sequence('string');
        $seq->append('apple', 'banana', 'cherry');

        // Test: Verify contains() returns true
        $this->assertTrue($seq->contains('banana'));
    }

    /**
     * Test contains method doesn't find non-existent value.
     */
    public function testContainsDoesNotFindNonExistentValue(): void
    {
        // Test: Check for value that doesn't exist
        $seq = new Sequence('string');
        $seq->append('apple', 'banana');

        // Test: Verify contains() returns false
        $this->assertFalse($seq->contains('cherry'));
    }

    /**
     * Test contains uses strict equality.
     */
    public function testContainsUsesStrictEquality(): void
    {
        // Test: Verify strict type checking
        $seq = new Sequence();
        $seq->append(1, '1', 2);

        // Test: Integer 1 is found
        $this->assertTrue($seq->contains(1));
        // Test: String '2' is not found (only integer 2 exists)
        $this->assertFalse($seq->contains('2'));
    }

    /**
     * Test contains finds NAN.
     */
    public function testContainsNan(): void
    {
        $seq = new Sequence(source: [1.0, NAN]);

        $this->assertTrue($seq->contains(NAN));
    }

    /**
     * Test contains distinguishes -0.0 from 0.0.
     */
    public function testContainsDistinguishesNegativeZero(): void
    {
        $seq = new Sequence(source: [0.0]);

        $this->assertTrue($seq->contains(0.0));
        $this->assertFalse($seq->contains(-0.0));
    }

    /**
     * Test contains compares floats nested in arrays by bit pattern.
     */
    public function testContainsWithNestedFloats(): void
    {
        $seq = new Sequence(source: [[1, NAN], [0.0]]);

        $this->assertTrue($seq->contains([1, NAN]));
        $this->assertFalse($seq->contains([-0.0]));
        $this->assertFalse($seq->contains([NAN, 1]));
    }

    /**
     * Test contains still distinguishes types.
     */
    public function testContainsDistinguishesTypes(): void
    {
        $seq = new Sequence(source: [1, '2', [3]]);

        $this->assertFalse($seq->contains(1.0));
        $this->assertFalse($seq->contains(2));
        $this->assertFalse($seq->contains([3.0]));
        $this->assertTrue($seq->contains([3]));
    }

    #endregion

    #region Method indexExists() tests.

    /**
     * Test indexExists method.
     */
    public function testIndexExists(): void
    {
        // Test: Check if index exists
        $seq = new Sequence('int');
        $seq->append(10, 20, 30);

        // Test: Valid indexes exist
        $this->assertTrue($seq->indexExists(0));
        $this->assertTrue($seq->indexExists(2));
        // Test: Invalid index doesn't exist
        $this->assertFalse($seq->indexExists(5));
    }

    #endregion

    #region Method empty() tests.

    /**
     * Test empty method returns true for empty Sequence.
     */
    public function testEmptyReturnsTrueForEmptySequence(): void
    {
        // Test: Check empty Sequence
        $seq = new Sequence('int');

        // Test: Verify empty() returns true
        $this->assertTrue($seq->empty());
    }

    /**
     * Test empty method returns false for non-empty Sequence.
     */
    public function testEmptyReturnsFalseForNonEmptySequence(): void
    {
        // Test: Check Sequence with items
        $seq = new Sequence('int');
        $seq->append(1, 2, 3);

        // Test: Verify empty() returns false
        $this->assertFalse($seq->empty());
    }

    #endregion

    #region Method all() tests.

    /**
     * Test all method returns true when all items pass test.
     */
    public function testAllReturnsTrueWhenAllPass(): void
    {
        // Test: Check if all items are positive
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);

        // Test: Verify all() returns true
        $this->assertTrue($seq->all(static fn ($x) => $x > 0));
    }

    /**
     * Test all method returns false when any item fails test.
     */
    public function testAllReturnsFalseWhenAnyFail(): void
    {
        // Test: Check if all items are even
        $seq = new Sequence('int');
        $seq->append(2, 4, 5, 6);

        // Test: Verify all() returns false (5 is odd)
        $this->assertFalse($seq->all(static fn ($x) => $x % 2 === 0));
    }

    #endregion

    #region Method any() tests.

    /**
     * Test any method returns true when at least one item passes test.
     */
    public function testAnyReturnsTrueWhenAtLeastOnePasses(): void
    {
        // Test: Check if any item is even
        $seq = new Sequence('int');
        $seq->append(1, 3, 4, 7);

        // Test: Verify any() returns true
        $this->assertTrue($seq->any(static fn ($x) => $x % 2 === 0));
    }

    /**
     * Test any method returns false when no items pass test.
     */
    public function testAnyReturnsFalseWhenNonePass(): void
    {
        // Test: Check if any item is greater than 10
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);

        // Test: Verify any() returns false
        $this->assertFalse($seq->any(static fn ($x) => $x > 10));
    }

    #endregion

    #region Method count() tests.

    /**
     * Test count method.
     */
    public function testCount(): void
    {
        // Test: Count items in Sequence
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);

        // Test: Verify count is correct
        $this->assertCount(5, $seq);
        $this->assertSame(5, $seq->count());
    }

    #endregion
}
