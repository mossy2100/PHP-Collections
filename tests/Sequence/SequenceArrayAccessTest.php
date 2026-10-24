<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Sequence;

use InvalidArgumentException;
use Mossy2100\Collections\Sequence;
use OutOfRangeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Sequence ArrayAccess methods.
 */
#[CoversClass(Sequence::class)]
class SequenceArrayAccessTest extends TestCase
{
    #region Method offsetExists() tests.

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

    #endregion

    #region Method offsetGet() tests.

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
     * Test the message for an index beyond the end.
     */
    public function testOutOfRangeIndexMessage(): void
    {
        $seq = new Sequence('int', [1, 2, 3]);

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('Invalid index: 5. Must be in the range 0-2.');
        $seq[5];
    }

    /**
     * Test the message for any index into an empty Sequence.
     */
    public function testEmptySequenceIndexMessage(): void
    {
        $seq = new Sequence('int');

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('Invalid index: 0. Sequence is empty.');
        $seq[0];
    }

    /**
     * Test the message for an index of the wrong type.
     */
    public function testIndexTypeMessage(): void
    {
        $seq = new Sequence('int', [1]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid index type: string. Must be int.');
        $seq['0'];
    }

    #endregion

    #region Method offsetSet() tests.

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
     * Test the message for a value of a disallowed type.
     */
    public function testValueTypeMessage(): void
    {
        $seq = new Sequence('int|float');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value type: string. Must be int|float.');
        $seq[] = 'x';
    }

    #endregion

    #region Method offsetUnset() tests.

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

    #endregion
}
