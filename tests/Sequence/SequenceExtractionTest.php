<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Sequence;

use LengthException;
use Mossy2100\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for Sequence extraction methods.
 */
#[CoversClass(Sequence::class)]
class SequenceExtractionTest extends TestCase
{
    #region Method first() tests.

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
     * Test first method throws LengthException on empty Sequence.
     */
    public function testFirstThrowsLengthExceptionOnEmptySequence(): void
    {
        // Test: Attempt to get first from empty Sequence
        $this->expectException(LengthException::class);

        $seq = new Sequence('int');
        $seq->first();
    }

    #endregion

    #region Method last() tests.

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
     * Test last method throws LengthException on empty Sequence.
     */
    public function testLastThrowsLengthExceptionOnEmptySequence(): void
    {
        // Test: Attempt to get last from empty Sequence
        $this->expectException(LengthException::class);

        $seq = new Sequence('int');
        $seq->last();
    }

    #endregion

    #region Method slice() tests.

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

    #endregion

    #region Method search() tests.

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
     * Test search finds NAN and distinguishes -0.0 from 0.0.
     */
    public function testSearchWithSpecialFloats(): void
    {
        $seq = new Sequence(source: [0.0, NAN, -0.0]);

        $this->assertSame(1, $seq->search(NAN));
        $this->assertSame(0, $seq->search(0.0));
        $this->assertSame(2, $seq->search(-0.0));
        $this->assertNull($seq->search(1.0));
    }

    #endregion

    #region Method find() tests.

    /**
     * Test find method returns first matching element.
     */
    public function testFindReturnsFirstMatchingElement(): void
    {
        // Test: Find first even number
        $seq = new Sequence('int');
        $seq->append(1, 3, 4, 6, 8);
        $result = $seq->find(static fn ($x) => $x % 2 === 0);

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
        $result = $seq->find(static fn ($x) => $x > 10);

        // Test: Verify null returned
        $this->assertNull($result);
    }

    #endregion

    #region Method unique() tests.

    /**
     * Test unique method removes duplicates.
     */
    public function testUniqueRemovesDuplicates(): void
    {
        // Test: Get unique values
        $seq = new Sequence('int');
        $seq->append(1, 2, 2, 3, 3, 3, 4, 5, 5);
        $unique = $seq->unique();

        // Test: Verify duplicates removed
        $this->assertCount(5, $unique);
        $this->assertTrue($unique->contains(1));
        $this->assertTrue($unique->contains(5));
    }

    /**
     * Test unique keeps the first occurrence of each value, in order.
     */
    public function testUniquePreservesOrder(): void
    {
        $seq = new Sequence(source: [3, 1, 3, 2, 1]);

        $this->assertSame([3, 1, 2], $seq->unique()->toArray());
    }

    /**
     * Test unique distinguishes values of different types.
     */
    public function testUniqueDistinguishesTypes(): void
    {
        $seq = new Sequence(source: [true, 1, 1.0, '1', 1, true]);

        $this->assertSame([true, 1, 1.0, '1'], $seq->unique()->toArray());
    }

    /**
     * Test unique treats NAN as equal to NAN.
     */
    public function testUniqueWithNan(): void
    {
        $seq = new Sequence(source: [NAN, 1.0, NAN]);
        $unique = $seq->unique();

        $this->assertCount(2, $unique);
        $this->assertNan($unique[0]);
        $this->assertSame(1.0, $unique[1]);
    }

    /**
     * Test unique treats -0.0 as distinct from 0.0.
     */
    public function testUniqueWithNegativeZero(): void
    {
        $seq = new Sequence(source: [0.0, -0.0, 0.0]);

        $this->assertCount(2, $seq->unique());
    }

    /**
     * Test unique gives the same values as toSet().
     */
    public function testUniqueMatchesToSet(): void
    {
        $seq = new Sequence(source: [NAN, NAN, 0.0, -0.0, 1, 1.0, true, 1]);

        $this->assertCount(count($seq->toSet()), $seq->unique());
    }

    /**
     * Test unique keeps object identity.
     */
    public function testUniqueWithObjects(): void
    {
        $a = new stdClass();
        $b = new stdClass();
        $seq = new Sequence(source: [$a, $b, $a]);
        $unique = $seq->unique();

        $this->assertCount(2, $unique);
        $this->assertSame($a, $unique[0]);
        $this->assertSame($b, $unique[1]);
    }

    #endregion
}
