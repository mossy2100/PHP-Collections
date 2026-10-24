<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Sequence;

use InvalidArgumentException;
use LengthException;
use Mossy2100\Collections\Collection;
use Mossy2100\Collections\Sequence;
use OutOfRangeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Sequence modification methods.
 */
#[CoversClass(Sequence::class)]
#[CoversClass(Collection::class)]
class SequenceModificationTest extends TestCase
{
    #region Method append() tests.

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
     * Test append throws InvalidArgumentException for invalid type.
     */
    public function testAppendThrowsInvalidArgumentException(): void
    {
        // Test: Attempt to append wrong type
        $this->expectException(InvalidArgumentException::class);

        $seq = new Sequence('int');
        $seq->append('string');
    }

    /**
     * Test append adds nothing if any item has a disallowed type.
     */
    public function testAppendIsAllOrNothing(): void
    {
        $seq = new Sequence('int', [1]);

        try {
            $seq->append(2, 'x', 3);
            $this->fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException) {
            // Test the Sequence is unchanged.
            $this->assertSame([1], $seq->toArray());
        }
    }

    #endregion

    #region Method prepend() tests.

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
     * Test prepend adds nothing if any item has a disallowed type.
     */
    public function testPrependIsAllOrNothing(): void
    {
        $seq = new Sequence('int', [1]);

        try {
            $seq->prepend(2, 'x', 3);
            $this->fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException) {
            // Test the Sequence is unchanged.
            $this->assertSame([1], $seq->toArray());
        }
    }

    /**
     * Test prepend keeps the order of the prepended items.
     */
    public function testPrependKeepsOrder(): void
    {
        $seq = new Sequence('int', [4]);

        $seq->prepend(1, 2, 3);

        $this->assertSame([1, 2, 3, 4], $seq->toArray());
    }

    #endregion

    #region Method insert() tests.

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

    #endregion

    #region Method import() tests.

    /**
     * Test import method with array.
     */
    public function testImportFromArray(): void
    {
        // Test: Import items from an array
        $seq = new Sequence('int');
        $seq->append(1, 2);
        $result = $seq->import([3, 4, 5]);

        // Test: Verify items were imported
        $this->assertSame($seq, $result); // Returns $this for chaining
        $this->assertCount(5, $seq);
        $this->assertSame(1, $seq[0]);
        $this->assertSame(5, $seq[4]);
    }

    /**
     * Test import method with empty iterable.
     */
    public function testImportFromEmptyIterable(): void
    {
        // Test: Import from empty array
        $seq = new Sequence('int');
        $seq->append(1, 2, 3);
        $seq->import([]);

        // Test: Verify sequence unchanged
        $this->assertCount(3, $seq);
    }

    /**
     * Test import method from another Sequence.
     */
    public function testImportFromAnotherSequence(): void
    {
        // Test: Import from another Sequence
        $seq1 = new Sequence('int');
        $seq1->append(1, 2, 3);

        $seq2 = new Sequence('int');
        $seq2->append(10, 20);
        $seq2->import($seq1);

        // Test: Verify items were imported
        $this->assertCount(5, $seq2);
        $this->assertSame(10, $seq2[0]);
        $this->assertSame(20, $seq2[1]);
        $this->assertSame(1, $seq2[2]);
        $this->assertSame(2, $seq2[3]);
        $this->assertSame(3, $seq2[4]);
        // Test: Original sequence unchanged
        $this->assertCount(3, $seq1);
    }

    /**
     * Test import method from generator.
     */
    public function testImportFromGenerator(): void
    {
        // Test: Import from a generator
        $generator = static function () {
            yield 10;
            yield 20;
            yield 30;
        };

        $seq = new Sequence('int');
        $seq->append(1, 2);
        $seq->import($generator());

        // Test: Verify items were imported from generator
        $this->assertCount(5, $seq);
        $this->assertSame(10, $seq[2]);
        $this->assertSame(30, $seq[4]);
    }

    /**
     * Test import preserves existing items.
     */
    public function testImportPreservesExistingItems(): void
    {
        // Test: Verify import doesn't clear existing items
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'c');
        $seq->import(['d', 'e']);

        // Test: Verify existing items preserved
        $this->assertCount(5, $seq);
        $this->assertSame('a', $seq[0]);
        $this->assertSame('b', $seq[1]);
        $this->assertSame('c', $seq[2]);
        $this->assertSame('d', $seq[3]);
        $this->assertSame('e', $seq[4]);
    }

    /**
     * Test import can be chained.
     */
    public function testImportCanBeChained(): void
    {
        // Test: Chain multiple import calls
        $seq = new Sequence('int');
        $seq->import([1, 2])
            ->import([3, 4])
            ->import([5, 6]);

        // Test: Verify all items imported
        $this->assertCount(6, $seq);
        $this->assertSame(1, $seq[0]);
        $this->assertSame(6, $seq[5]);
    }

    /**
     * Test import throws InvalidArgumentException for invalid types.
     */
    public function testImportThrowsInvalidArgumentExceptionForInvalidTypes(): void
    {
        // Test: Attempt to import items with wrong type
        $this->expectException(InvalidArgumentException::class);

        $seq = new Sequence('int');
        $seq->import(['string', 'values']);
    }

    /**
     * Test import with mixed types in untyped Sequence.
     */
    public function testImportWithMixedTypesInUntypedSequence(): void
    {
        // Test: Import mixed types into untyped Sequence
        $seq = new Sequence();
        $seq->import([1, 'two', 3.0, true, null]);

        // Test: Verify all types accepted
        $this->assertCount(5, $seq);
        $this->assertSame(1, $seq[0]);
        $this->assertSame('two', $seq[1]);
        $this->assertSame(3.0, $seq[2]);
        $this->assertTrue($seq[3]);
        $this->assertNull($seq[4]);
    }

    /**
     * Test import adds nothing if any value has a disallowed type.
     */
    public function testImportIsAllOrNothing(): void
    {
        // Test: Verify nothing is imported if any value is invalid
        $seq = new Sequence('int');
        $seq->append(1, 2);

        try {
            $seq->import([3, 4, 'invalid', 5, 6]);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (InvalidArgumentException) {
            // Test: Verify the Sequence is unchanged
            $this->assertSame([1, 2], $seq->toArray());
        }
    }

    /**
     * Test import into Sequence with union types.
     */
    public function testImportIntoSequenceWithUnionTypes(): void
    {
        // Test: Import into Sequence with union type constraint
        $seq = new Sequence('int|string');
        $seq->import([1, 'two', 3, 'four']);

        // Test: Verify both types accepted
        $this->assertCount(4, $seq);
        $this->assertSame(1, $seq[0]);
        $this->assertSame('two', $seq[1]);
        $this->assertSame(3, $seq[2]);
        $this->assertSame('four', $seq[3]);
    }

    /**
     * Test import with associative array ignores keys.
     */
    public function testImportWithAssociativeArrayIgnoresKeys(): void
    {
        // Test: Import from associative array
        $seq = new Sequence('string');
        $seq->import([
            'a' => 'apple',
            'b' => 'banana',
            'c' => 'cherry',
        ]);

        // Test: Verify values imported with sequential indexes
        $this->assertCount(3, $seq);
        $this->assertSame('apple', $seq[0]);
        $this->assertSame('banana', $seq[1]);
        $this->assertSame('cherry', $seq[2]);
    }

    /**
     * Test import into empty Sequence.
     */
    public function testImportIntoEmptySequence(): void
    {
        // Test: Import into a new empty Sequence
        $seq = new Sequence('int');
        $seq->import([10, 20, 30]);

        // Test: Verify items imported
        $this->assertCount(3, $seq);
        $this->assertSame(10, $seq[0]);
        $this->assertSame(30, $seq[2]);
    }

    /**
     * Test import with spread operator in source array.
     */
    public function testImportWithSpreadOperator(): void
    {
        // Test: Import multiple arrays using spread
        $seq = new Sequence('int');
        $arr1 = [1, 2, 3];
        $arr2 = [4, 5, 6];
        $seq->import([...$arr1, ...$arr2]);

        // Test: Verify all items imported
        $this->assertCount(6, $seq);
        $this->assertSame(1, $seq[0]);
        $this->assertSame(6, $seq[5]);
    }

    #endregion

    #region Method removeByIndex() tests.

    /**
     * Test removeByIndex method.
     */
    public function testRemoveByIndex(): void
    {
        // Test: Remove item by index
        $seq = new Sequence('int');
        $seq->append(10, 20, 30, 40, 50);
        $removedItem = $seq->removeByIndex(2);

        // Test: Verify item was removed and returned
        $this->assertEquals(30, $removedItem);
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

    #endregion

    #region Method removeByValue() tests.

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
     * Test removeByValue removes NAN values.
     */
    public function testRemoveByValueNan(): void
    {
        $seq = new Sequence(source: [NAN, 1.0, NAN]);

        $this->assertSame(2, $seq->removeByValue(NAN));
        $this->assertSame([1.0], $seq->toArray());
    }

    /**
     * Test removeByValue distinguishes -0.0 from 0.0.
     */
    public function testRemoveByValueNegativeZero(): void
    {
        $seq = new Sequence(source: [0.0, -0.0, 0.0]);

        $this->assertSame(1, $seq->removeByValue(-0.0));
        $this->assertCount(2, $seq);
        $this->assertFalse($seq->contains(-0.0));
    }

    #endregion

    #region Method removeFirst() tests.

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
     * Test removeFirst throws LengthException on empty Sequence.
     */
    public function testRemoveFirstThrowsLengthException(): void
    {
        // Test: Attempt to remove first from empty Sequence
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('Cannot remove first item from an empty Sequence');

        $seq = new Sequence('int');
        $seq->removeFirst();
    }

    #endregion

    #region Method removeLast() tests.

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
     * Test removeLast throws LengthException on empty Sequence.
     */
    public function testRemoveLastThrowsLengthException(): void
    {
        // Test: Attempt to remove last from empty Sequence
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('Cannot remove last item from an empty Sequence');

        $seq = new Sequence('int');
        $seq->removeLast();
    }

    #endregion

    #region Method clear() tests.

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

    #endregion
}
