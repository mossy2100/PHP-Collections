<?php

declare(strict_types=1);

namespace Galaxon\Collections\Tests\Sequence;

use Galaxon\Collections\Sequence;
use OutOfRangeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Sequence inspection and getter methods.
 */
#[CoversClass(Sequence::class)]
class SequenceInspectionTest extends TestCase
{
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

    /**
     * Test first method returns first item.
     */
    public function testFirstReturnsFirstItem(): void
    {
        // Test: Get first item
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'c');

        // Test: Verify first() returns correct item
        $this->assertSame('a', $seq->first());
    }

    /**
     * Test first method throws exception on empty Sequence.
     */
    public function testFirstThrowsExceptionOnEmptySequence(): void
    {
        // Test: Attempt to get first from empty Sequence
        $this->expectException(OutOfRangeException::class);

        $seq = new Sequence('int');
        $seq->first();
    }

    /**
     * Test last method returns last item.
     */
    public function testLastReturnsLastItem(): void
    {
        // Test: Get last item
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'c');

        // Test: Verify last() returns correct item
        $this->assertSame('c', $seq->last());
    }

    /**
     * Test last method throws exception on empty Sequence.
     */
    public function testLastThrowsExceptionOnEmptySequence(): void
    {
        // Test: Attempt to get last from empty Sequence
        $this->expectException(OutOfRangeException::class);

        $seq = new Sequence('int');
        $seq->last();
    }

    /**
     * Test slice method with positive index and length.
     */
    public function testSliceWithPositiveIndexAndLength(): void
    {
        // Test: Get a slice from the middle
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $slice = $seq->slice(3, 4);

        // Test: Verify slice contains correct items
        $this->assertCount(4, $slice);
        $this->assertSame(4, $slice[0]);
        $this->assertSame(7, $slice[3]);
    }

    /**
     * Test slice method with negative index.
     */
    public function testSliceWithNegativeIndex(): void
    {
        // Test: Get slice from end using negative index
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $slice = $seq->slice(-3);

        // Test: Verify slice contains last 3 items
        $this->assertCount(3, $slice);
        $this->assertSame(3, $slice[0]);
        $this->assertSame(5, $slice[2]);
    }

    /**
     * Test slice method with negative length.
     */
    public function testSliceWithNegativeLength(): void
    {
        // Test: Slice with negative length stops before end
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5, 6, 7, 8);
        $slice = $seq->slice(2, -2);

        // Test: Verify slice stops 2 elements from end
        $this->assertCount(4, $slice);
        $this->assertSame(3, $slice[0]);
        $this->assertSame(6, $slice[3]);
    }

    /**
     * Test search method finds value.
     */
    public function testSearchFindsValue(): void
    {
        // Test: Search for existing value
        $seq = new Sequence('string');
        $seq->append('apple', 'banana', 'cherry', 'date');
        $index = $seq->search('cherry');

        // Test: Verify correct index returned
        $this->assertSame(2, $index);
    }

    /**
     * Test search method returns null when value not found.
     */
    public function testSearchReturnsNullWhenNotFound(): void
    {
        // Test: Search for non-existent value
        $seq = new Sequence('string');
        $seq->append('apple', 'banana');
        $index = $seq->search('grape');

        // Test: Verify null returned
        $this->assertNull($index);
    }

    /**
     * Test search uses strict equality.
     */
    public function testSearchUsesStrictEquality(): void
    {
        // Test: Verify strict type checking in search
        $seq = new Sequence();
        $seq->append(1, 2, '3', 4);

        // Test: String '3' is at index 2
        $this->assertSame(2, $seq->search('3'));
        // Test: Integer 3 is not found
        $this->assertNull($seq->search(3));
    }

    /**
     * Test find method returns first matching element.
     */
    public function testFindReturnsFirstMatchingElement(): void
    {
        // Test: Find first even number
        $seq = new Sequence('int');
        $seq->append(1, 3, 4, 6, 8);
        $result = $seq->find(fn($x) => $x % 2 === 0);

        // Test: Verify first even number returned
        $this->assertSame(4, $result);
    }

    /**
     * Test find method returns null when no match.
     */
    public function testFindReturnsNullWhenNoMatch(): void
    {
        // Test: Try to find element that doesn't exist
        $seq = new Sequence('int');
        $seq->append(1, 3, 5, 7);
        $result = $seq->find(fn($x) => $x > 10);

        // Test: Verify null returned
        $this->assertNull($result);
    }

    /**
     * Test all method returns true when all items pass test.
     */
    public function testAllReturnsTrueWhenAllPass(): void
    {
        // Test: Check if all items are positive
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);

        // Test: Verify all() returns true
        $this->assertTrue($seq->all(fn($x) => $x > 0));
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
        $this->assertFalse($seq->all(fn($x) => $x % 2 === 0));
    }

    /**
     * Test any method returns true when at least one item passes test.
     */
    public function testAnyReturnsTrueWhenAtLeastOnePasses(): void
    {
        // Test: Check if any item is even
        $seq = new Sequence('int');
        $seq->append(1, 3, 4, 7);

        // Test: Verify any() returns true
        $this->assertTrue($seq->any(fn($x) => $x % 2 === 0));
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
        $this->assertFalse($seq->any(fn($x) => $x > 10));
    }

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
}
