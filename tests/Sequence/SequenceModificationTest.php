<?php

declare(strict_types=1);

namespace Galaxon\Collections\Tests\Sequence;

use Galaxon\Collections\Sequence;
use OutOfRangeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;
use UnderflowException;

/**
 * Tests for Sequence modification methods (add and remove items).
 */
#[CoversClass(Sequence::class)]
class SequenceModificationTest extends TestCase
{
    /**
     * Test append method with single item.
     */
    public function testAppendSingleItem(): void
    {
        // Test: Create a Sequence and append one item
        $seq = new Sequence('int');
        $result = $seq->append(10);

        // Test: Verify item was appended
        $this->assertSame($seq, $result); // Returns $this for chaining
        $this->assertCount(1, $seq);
        $this->assertSame(10, $seq[0]);
    }

    /**
     * Test append method with multiple items.
     */
    public function testAppendMultipleItems(): void
    {
        // Test: Append multiple items at once
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'c');

        // Test: Verify all items were appended
        $this->assertCount(3, $seq);
        $this->assertSame('a', $seq[0]);
        $this->assertSame('b', $seq[1]);
        $this->assertSame('c', $seq[2]);
    }

    /**
     * Test append method with spread operator.
     */
    public function testAppendWithSpreadOperator(): void
    {
        // Test: Append items using spread operator
        $seq = new Sequence('int');
        $items = [1, 2, 3, 4, 5];
        $seq->append(...$items);

        // Test: Verify all items were appended
        $this->assertCount(5, $seq);
        $this->assertSame(5, $seq[4]);
    }

    /**
     * Test append throws TypeError for invalid type.
     */
    public function testAppendThrowsTypeError(): void
    {
        // Test: Attempt to append wrong type
        $this->expectException(TypeError::class);

        $seq = new Sequence('int');
        $seq->append('string');
    }

    /**
     * Test prepend method with single item.
     */
    public function testPrependSingleItem(): void
    {
        // Test: Prepend item to Sequence
        $seq = new Sequence('int');
        $seq->append(2, 3);
        $result = $seq->prepend(1);

        // Test: Verify item was prepended
        $this->assertSame($seq, $result); // Returns $this for chaining
        $this->assertCount(3, $seq);
        $this->assertSame(1, $seq[0]);
        $this->assertSame(2, $seq[1]);
    }

    /**
     * Test prepend method with multiple items.
     */
    public function testPrependMultipleItems(): void
    {
        // Test: Prepend multiple items
        $seq = new Sequence('string');
        $seq->append('d');
        $seq->prepend('a', 'b', 'c');

        // Test: Verify items were prepended in order
        $this->assertCount(4, $seq);
        $this->assertSame('a', $seq[0]);
        $this->assertSame('b', $seq[1]);
        $this->assertSame('c', $seq[2]);
        $this->assertSame('d', $seq[3]);
    }

    /**
     * Test insert method at beginning.
     */
    public function testInsertAtBeginning(): void
    {
        // Test: Insert item at index 0
        $seq = new Sequence('int');
        $seq->append(2, 3, 4);
        $seq->insert(0, 1);

        // Test: Verify item was inserted at beginning
        $this->assertCount(4, $seq);
        $this->assertSame(1, $seq[0]);
        $this->assertSame(2, $seq[1]);
    }

    /**
     * Test insert method at middle.
     */
    public function testInsertAtMiddle(): void
    {
        // Test: Insert item in middle of Sequence
        $seq = new Sequence('int');
        $seq->append(1, 2, 4, 5);
        $seq->insert(2, 3);

        // Test: Verify item was inserted correctly
        $this->assertCount(5, $seq);
        $this->assertSame(3, $seq[2]);
        $this->assertSame(4, $seq[3]);
    }

    /**
     * Test insert method beyond end fills gaps with defaults.
     */
    public function testInsertBeyondEndFillsGaps(): void
    {
        // Test: Insert item beyond current length
        $seq = new Sequence('int');
        $seq->append(1, 2);
        $seq->insert(5, 10);

        // Test: Verify gaps were filled with default value (0)
        $this->assertCount(6, $seq);
        $this->assertSame(0, $seq[2]);
        $this->assertSame(0, $seq[3]);
        $this->assertSame(0, $seq[4]);
        $this->assertSame(10, $seq[5]);
    }

    /**
     * Test insert throws OutOfRangeException for negative index.
     */
    public function testInsertThrowsOutOfRangeForNegativeIndex(): void
    {
        // Test: Attempt to insert at negative index
        $this->expectException(OutOfRangeException::class);

        $seq = new Sequence('int');
        $seq->insert(-1, 10);
    }

    /**
     * Test clear method.
     */
    public function testClear(): void
    {
        // Test: Clear a Sequence with items
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $result = $seq->clear();

        // Test: Verify Sequence is empty
        $this->assertSame($seq, $result); // Returns $this for chaining
        $this->assertCount(0, $seq);
        $this->assertTrue($seq->empty());
    }

    /**
     * Test removeByIndex method.
     */
    public function testRemoveByIndex(): void
    {
        // Test: Remove item by index
        $seq = new Sequence('int');
        $seq->append(10, 20, 30, 40, 50);
        $removed_item = $seq->removeByIndex(2);

        // Test: Verify item was removed and returned
        $this->assertEquals(30, $removed_item);
        $this->assertCount(4, $seq);
        $this->assertSame(40, $seq[2]); // Next item shifted down
    }

    /**
     * Test removeByIndex throws OutOfRangeException for invalid index.
     */
    public function testRemoveByIndexThrowsOutOfRange(): void
    {
        // Test: Attempt to remove at invalid index
        $this->expectException(OutOfRangeException::class);

        $seq = new Sequence('int');
        $seq->append(1, 2, 3);
        $seq->removeByIndex(5);
    }

    /**
     * Test removeByValue method removes all matching values.
     */
    public function testRemoveByValue(): void
    {
        // Test: Remove all items matching value
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 2, 4, 2, 5);
        $seq->removeByValue(2);

        // Test: Verify all matching values were removed
        $this->assertCount(4, $seq);
        $this->assertFalse($seq->contains(2));
    }

    /**
     * Test removeByValue returns zero when value not found.
     */
    public function testRemoveByValueNotFound(): void
    {
        // Test: Attempt to remove non-existent value
        $seq = new Sequence('int');
        $seq->append(1, 2, 3);
        $seq->removeByValue(10);

        // Test: Verify nothing was removed
        $this->assertCount(3, $seq);
    }

    /**
     * Test removeFirst method.
     */
    public function testRemoveFirst(): void
    {
        // Test: Remove first item
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'c');
        $removed = $seq->removeFirst();

        // Test: Verify first item was removed
        $this->assertSame('a', $removed);
        $this->assertCount(2, $seq);
        $this->assertSame('b', $seq[0]);
    }

    /**
     * Test removeFirst throws UnderflowException on empty Sequence.
     */
    public function testRemoveFirstThrowsUnderflow(): void
    {
        // Test: Attempt to remove first from empty Sequence
        $this->expectException(UnderflowException::class);
        $this->expectExceptionMessage("No items in the Sequence");

        $seq = new Sequence('int');
        $seq->removeFirst();
    }

    /**
     * Test removeLast method.
     */
    public function testRemoveLast(): void
    {
        // Test: Remove last item
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'c');
        $removed = $seq->removeLast();

        // Test: Verify last item was removed
        $this->assertSame('c', $removed);
        $this->assertCount(2, $seq);
        $this->assertSame('b', $seq[1]);
    }

    /**
     * Test removeLast throws UnderflowException on empty Sequence.
     */
    public function testRemoveLastThrowsUnderflow(): void
    {
        // Test: Attempt to remove last from empty Sequence
        $this->expectException(UnderflowException::class);
        $this->expectExceptionMessage("No items in the Sequence");

        $seq = new Sequence('int');
        $seq->removeLast();
    }
}
