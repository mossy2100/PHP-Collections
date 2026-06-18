<?php

declare(strict_types=1);

namespace OceanMoon\Collections\Tests\Sequence;

use DomainException;
use InvalidArgumentException;
use LengthException;
use OceanMoon\Collections\Sequence;
use OutOfRangeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Sequence ArrayAccess implementation and random methods.
 */
#[CoversClass(Sequence::class)]
class SequenceArrayAccessTest extends TestCase
{
    /**
     * Test offsetSet with null appends item.
     */
    public function testOffsetSetWithNullAppendsItem(): void
    {
        // Test: Use [] syntax to append
        $seq = new Sequence('int');
        $seq[] = 10;
        $seq[] = 20;

        // Test: Verify items were appended
        $this->assertCount(2, $seq);
        $this->assertSame(10, $seq[0]);
        $this->assertSame(20, $seq[1]);
    }

    /**
     * Test offsetSet with valid index.
     */
    public function testOffsetSetWithValidIndex(): void
    {
        // Test: Set item at specific index
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'c');
        $seq[1] = 'x';

        // Test: Verify item was set
        $this->assertSame('x', $seq[1]);
    }

    /**
     * Test offsetSet beyond range fills gaps.
     */
    public function testOffsetSetBeyondRangeFillsGaps(): void
    {
        // Test: Set item beyond current range
        $seq = new Sequence('int');
        $seq->append(1, 2);
        $seq[5] = 99;

        // Test: Verify gaps filled with default (0)
        $this->assertCount(6, $seq);
        $this->assertSame(0, $seq[2]);
        $this->assertSame(0, $seq[3]);
        $this->assertSame(0, $seq[4]);
        $this->assertSame(99, $seq[5]);
    }

    /**
     * Test offsetSet throws InvalidArgumentException for invalid type.
     */
    public function testOffsetSetThrowsInvalidArgumentException(): void
    {
        // Test: Attempt to set wrong type
        $this->expectException(InvalidArgumentException::class);

        $seq = new Sequence('int');
        $seq[0] = 'Some string';
    }

    /**
     * Test offsetSet throws InvalidArgumentException for non-integer index.
     */
    public function testOffsetSetThrowsInvalidArgumentExceptionForNonIntegerIndex(): void
    {
        // Test: Attempt to use string as index
        $this->expectException(InvalidArgumentException::class);

        $seq = new Sequence('int');
        $seq['key'] = 10;
    }

    /**
     * Test offsetSet throws OutOfRangeException for negative index.
     */
    public function testOffsetSetThrowsOutOfRangeForNegativeIndex(): void
    {
        // Test: Attempt to use negative index
        $this->expectException(OutOfRangeException::class);

        $seq = new Sequence('int');
        $seq[-1] = 10;
    }

    /**
     * Test offsetGet retrieves item.
     */
    public function testOffsetGetRetrievesItem(): void
    {
        // Test: Get item using array syntax
        $seq = new Sequence('string');
        $seq->append('apple', 'banana', 'cherry');

        // Test: Verify correct item retrieved
        $this->assertSame('banana', $seq[1]);
    }

    /**
     * Test offsetGet throws OutOfRangeException for invalid index.
     */
    public function testOffsetGetThrowsOutOfRange(): void
    {
        // Test: Attempt to get item at invalid index
        $this->expectException(OutOfRangeException::class);

        $seq = new Sequence('int');
        $seq->append(1, 2, 3);
        $value = $seq[10];
    }

    /**
     * Test offsetExists returns true for valid index.
     */
    public function testOffsetExistsReturnsTrueForValidIndex(): void
    {
        // Test: Check if index exists
        $seq = new Sequence('int');
        $seq->append(1, 2, 3);

        // Test: Verify isset works correctly
        $this->assertTrue(isset($seq[0]));
        $this->assertTrue(isset($seq[2]));
        $this->assertFalse(isset($seq[3]));
    }

    /**
     * Test offsetExists throws InvalidArgumentException for non-integer index.
     */
    public function testOffsetExistsThrowsInvalidArgumentException(): void
    {
        // Test: Attempt to check string index
        $this->expectException(InvalidArgumentException::class);

        $seq = new Sequence('int');
        $exists = isset($seq['key']);
    }

    /**
     * Test offsetUnset sets item to null.
     */
    public function testOffsetUnsetSetsItemToNull(): void
    {
        // Test: Unset item in nullable Sequence
        $seq = new Sequence('?int');
        $seq->append(1, 2, 3);
        unset($seq[1]);

        // Test: Verify item is now null but count unchanged
        $this->assertCount(3, $seq);
        $this->assertNull($seq[1]);
    }

    /**
     * Test offsetUnset fills gap with inferred default value.
     */
    public function testOffsetUnsetFillsGapWithDefault(): void
    {
        // Test: Unset item in non-nullable Sequence
        $seq = new Sequence('int');
        $seq->append(1, 2, 3);
        unset($seq[1]);

        // Test: Verify gap is filled with inferred default (0 for int) and count unchanged
        $this->assertCount(3, $seq);
        $this->assertSame(0, $seq[1]);
    }

    /**
     * Test iteration with foreach.
     */
    public function testIterationWithForeach(): void
    {
        // Test: Iterate using foreach
        $seq = new Sequence('int');
        $seq->append(10, 20, 30);

        $sum = 0;
        foreach ($seq as $value) {
            /** @var int $value */
            $sum += $value;
        }

        // Test: Verify iteration worked
        $this->assertSame(60, $sum);
    }

    /**
     * Test iteration preserves indexes.
     */
    public function testIterationPreservesIndexes(): void
    {
        // Test: Iterate with index
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'c');

        $indexes = [];
        foreach ($seq as $index => $value) {
            $indexes[] = $index;
        }

        // Test: Verify indexes are correct
        $this->assertSame([0, 1, 2], $indexes);
    }

    /**
     * Test chooseRand with single item.
     */
    public function testChooseRandSingleItem(): void
    {
        // Test: Choose one random item
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $chosen = $seq->chooseRand(1);

        // Test: Verify one item chosen
        $this->assertCount(1, $chosen);
        // Test: Verify chosen item is from sequence
        $this->assertIsInt($chosen[0]);
        $this->assertTrue($seq->contains($chosen[0]));
        // Test: Original sequence unchanged
        $this->assertCount(5, $seq);
    }

    /**
     * Test chooseRand with multiple items.
     */
    public function testChooseRandMultipleItems(): void
    {
        // Test: Choose multiple random items
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $chosen = $seq->chooseRand(3);

        // Test: Verify correct number chosen
        $this->assertCount(3, $chosen);
        // Test: Verify all chosen items are from sequence
        foreach ($chosen as $value) {
            $this->assertTrue($seq->contains($value));
        }
    }

    /**
     * Test chooseRand throws DomainException when count is negative.
     */
    public function testChooseRandThrowsDomainExceptionWhenCountNegative(): void
    {
        // Test: Attempt to choose with negative count
        $this->expectException(DomainException::class);

        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $seq->chooseRand(-1);
    }

    /**
     * Test chooseRand throws LengthException on empty Sequence.
     */
    public function testChooseRandThrowsLengthExceptionOnEmpty(): void
    {
        // Test: Attempt to choose from empty Sequence
        $this->expectException(LengthException::class);

        $seq = new Sequence('int');
        $seq->chooseRand();
    }

    /**
     * Test chooseRand throws LengthException when count too large.
     */
    public function testChooseRandThrowsLengthExceptionWhenCountTooLarge(): void
    {
        // Test: Attempt to choose more items than available
        $this->expectException(LengthException::class);

        $seq = new Sequence('int');
        $seq->append(1, 2, 3);
        $seq->chooseRand(5);
    }

    /**
     * Test removeRand removes item.
     */
    public function testRemoveRandRemovesItem(): void
    {
        // Test: Remove one random item
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $removed = $seq->removeRand(1);

        // Test: Verify item removed
        $this->assertCount(1, $removed);
        $this->assertCount(4, $seq);
        // Test: Verify removed item no longer in sequence
        $this->assertFalse($seq->contains($removed[0]));
    }

    /**
     * Test removeRand with multiple items.
     */
    public function testRemoveRandMultipleItems(): void
    {
        // Test: Remove multiple random items
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $originalCount = $seq->count();
        $removed = $seq->removeRand(3);

        // Test: Verify correct number removed
        $this->assertCount(3, $removed);
        $this->assertCount($originalCount - 3, $seq);
    }

    /**
     * Test toArray conversion.
     */
    public function testToArray(): void
    {
        // Test: Convert Sequence to array
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $array = $seq->toArray();

        // Test: Verify conversion
        $this->assertIsArray($array);
        $this->assertSame([1, 2, 3, 4, 5], $array);
    }
}
