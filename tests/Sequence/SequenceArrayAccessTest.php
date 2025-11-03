<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Sequence;

use DateTime;
use Galaxon\Collections\Sequence;
use OutOfRangeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;
use UnderflowException;

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
     * Test offsetSet throws TypeError for invalid type.
     */
    public function testOffsetSetThrowsTypeError(): void
    {
        // Test: Attempt to set wrong type
        $this->expectException(TypeError::class);

        $seq = new Sequence('int');
        $seq[0] = 'Some string';
    }

    /**
     * Test offsetSet throws TypeError for non-integer index.
     */
    public function testOffsetSetThrowsTypeErrorForNonIntegerIndex(): void
    {
        // Test: Attempt to use string as index
        $this->expectException(TypeError::class);

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
     * Test offsetExists throws TypeError for non-integer index.
     */
    public function testOffsetExistsThrowsTypeError(): void
    {
        // Test: Attempt to check string index
        $this->expectException(TypeError::class);

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
     * Test offsetUnset sets item to default value.
     */
    public function testOffsetUnsetSetsItemToDefaultValue(): void
    {
        // Test: Unset item in non-nullable Sequence
        $seq = new Sequence('int');
        $seq->append(1, 2, 3);
        unset($seq[1]);

        // Test: Verify item is now the default value (0) and count unchanged.
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
        $key = array_key_first($chosen);
        $this->assertTrue($seq->contains($chosen[$key]));
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
     * Test chooseRand throws UnderflowException on empty Sequence.
     */
    public function testChooseRandThrowsOutOfRangeWhenCountNegative(): void
    {
        // Test: Attempt to choose from empty Sequence
        $this->expectException(OutOfRangeException::class);

        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $seq->chooseRand(-1);
    }

    /**
     * Test chooseRand throws UnderflowException on empty Sequence.
     */
    public function testChooseRandThrowsUnderflowOnEmpty(): void
    {
        // Test: Attempt to choose from empty Sequence
        $this->expectException(UnderflowException::class);

        $seq = new Sequence('int');
        $seq->chooseRand();
    }

    /**
     * Test chooseRand throws OutOfRangeException when count too large.
     */
    public function testChooseRandThrowsOutOfRangeWhenCountTooLarge(): void
    {
        // Test: Attempt to choose more items than available
        $this->expectException(OutOfRangeException::class);

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
     * Test object cloning in default values.
     */
    public function testObjectCloningInDefaultValues(): void
    {
        // Test: Verify objects are cloned, not shared
        $defaultDate = new DateTime('2025-01-01');
        $seq = new Sequence('DateTime', $defaultDate);
        $seq[2] = new DateTime('2025-01-15'); // Fill gaps with clones

        // Test: Verify default objects are clones
        $this->assertCount(3, $seq);
        $date0 = $seq[0];
        $date1 = $seq[1];
        $this->assertNotSame($date0, $date1); // Different objects
        $this->assertEquals($date0, $date1); // Same value
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
