<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Sequence;

use DomainException;
use Mossy2100\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * Tests for Sequence transformation methods.
 */
#[CoversClass(Sequence::class)]
class SequenceTransformationTest extends TestCase
{
    #region Method chunk() tests.

    /**
     * Test chunk method splits into equal parts.
     */
    public function testChunkSplitsIntoEqualParts(): void
    {
        // Test: Split into chunks of 3
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5, 6, 7, 8, 9);
        $chunks = $seq->chunk(3);

        // Test: Verify 3 chunks created
        $this->assertCount(3, $chunks);
        $this->assertCount(3, $chunks[0]);
        $this->assertSame(1, $chunks[0][0]);
        $this->assertSame(9, $chunks[2][2]);
    }

    /**
     * Test chunk method handles remainder.
     */
    public function testChunkHandlesRemainder(): void
    {
        // Test: Split with uneven division
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5, 6, 7, 8);
        $chunks = $seq->chunk(3);

        // Test: Verify last chunk is smaller
        $this->assertCount(3, $chunks);
        $this->assertCount(3, $chunks[0]);
        $this->assertCount(2, $chunks[2]); // Last chunk has remainder
    }

    /**
     * Test chunk method throws an exception when the size argument is invalid.
     */
    public function testChunkThrowsOnInvalidSize(): void
    {
        // Test: Split with uneven division
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5, 6, 7, 8);

        // Check for exception.
        $this->expectException(DomainException::class);
        $chunks = $seq->chunk(0);
    }

    #endregion

    #region Method fill() tests.

    /**
     * Test fill method fills with value.
     */
    public function testFillWithValue(): void
    {
        // Test: Fill portion with specific value
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $seq->fill(1, 3, 99);

        // Test: Verify filled correctly
        $this->assertSame(1, $seq[0]);
        $this->assertSame(99, $seq[1]);
        $this->assertSame(99, $seq[2]);
        $this->assertSame(99, $seq[3]);
        $this->assertSame(5, $seq[4]);
    }

    /**
     * Test fill method with value.
     */
    public function testFillWithValue2(): void
    {
        // Test: Fill with value.
        $seq = new Sequence('int');
        $seq->fill(0, 5, 99);

        // Test: Verify filled with specified value.
        $this->assertCount(5, $seq);
        $this->assertSame(99, $seq[0]);
        $this->assertSame(99, $seq[4]);
    }

    #endregion

    #region Method filter() tests.

    /**
     * Test filter method keeps matching items.
     */
    public function testFilterKeepsMatchingItems(): void
    {
        // Test: Filter even numbers
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5, 6, 7, 8);
        $filtered = $seq->filter(static fn ($x) => $x % 2 === 0);

        // Test: Verify only even numbers remain
        $this->assertCount(4, $filtered);
        $this->assertSame(2, $filtered[0]);
        $this->assertSame(8, $filtered[3]);
    }

    /**
     * Test filter method returns empty Sequence when nothing matches.
     */
    public function testFilterReturnsEmptyWhenNothingMatches(): void
    {
        // Test: Filter for values greater than 10
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $filtered = $seq->filter(static fn ($x) => $x > 10);

        // Test: Verify empty Sequence returned
        $this->assertCount(0, $filtered);
    }

    /**
     * Test filter re-indexes the kept items and preserves types.
     */
    public function testFilterReindexesAndPreservesTypes(): void
    {
        $seq = new Sequence('int', [1, 2, 3, 4]);
        $filtered = $seq->filter(static fn ($x) => $x > 2);

        // Test: Verify indexes start from 0 and types are kept
        $this->assertSame([3, 4], $filtered->toArray());
        $this->assertSame(3, $filtered[0]);
        $this->assertTrue($filtered->valueTypes->containsOnly('int'));
    }

    /**
     * Test filter throws if the callback doesn't return a bool.
     */
    public function testFilterCallbackMustReturnBool(): void
    {
        $seq = new Sequence('int', [1, 2, 3]);

        // Test: A truthy int is not accepted
        $this->expectException(UnexpectedValueException::class);
        $seq->filter(static fn ($x) => $x - 1);
    }

    #endregion

    #region Method map() tests.

    /**
     * Test map method transforms items.
     */
    public function testMapTransformsItems(): void
    {
        // Test: Double each value
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $mapped = $seq->map(static fn ($x) => $x * 2);

        // Test: Verify transformation applied
        $this->assertCount(5, $mapped);
        $this->assertSame(2, $mapped[0]);
        $this->assertSame(10, $mapped[4]);
    }

    /**
     * Test map method can change types.
     */
    public function testMapCanChangeTypes(): void
    {
        // Test: Convert integers to strings
        $seq = new Sequence('int');
        $seq->append(1, 2, 3);
        $mapped = $seq->map(static fn ($x) => "Number $x");

        // Test: Verify type changed
        $this->assertSame('Number 1', $mapped[0]);
        $this->assertSame('Number 3', $mapped[2]);
    }

    #endregion

    #region Method merge() tests.

    /**
     * Test merge method combines two sequences.
     */
    public function testMergeCombinesTwoSequences(): void
    {
        // Test: Merge two sequences
        $seq1 = new Sequence('int');
        $seq1->append(1, 2, 3);
        $seq2 = new Sequence('int');
        $seq2->append(4, 5, 6);
        $merged = $seq1->merge($seq2);

        // Test: Verify items from both sequences present
        $this->assertCount(6, $merged);
        $this->assertSame(1, $merged[0]);
        $this->assertSame(6, $merged[5]);
    }

    /**
     * Test merge combines the type constraints of both sequences.
     */
    public function testMergeCombinesTypes(): void
    {
        $seq1 = new Sequence('int', [1]);
        $seq2 = new Sequence('string', ['a']);

        $merged = $seq1->merge($seq2);

        $this->assertSame([1, 'a'], $merged->toArray());
        $this->assertTrue($merged->valueTypes->containsOnly('int', 'string'));
    }

    /**
     * Test merge with an unconstrained Sequence allows any type, whichever side it's on.
     */
    public function testMergeWithUnconstrainedSequence(): void
    {
        $typed = new Sequence('int', [1]);
        $any = new Sequence(null, ['a']);

        $this->assertTrue($typed->merge($any)->valueTypes->anyOk());
        $this->assertTrue($any->merge($typed)->valueTypes->anyOk());
    }

    /**
     * Test merge doesn't modify either sequence.
     */
    public function testMergeIsNonMutating(): void
    {
        $seq1 = new Sequence('int', [1]);
        $seq2 = new Sequence('string', ['a']);

        $seq1->merge($seq2);

        $this->assertSame([1], $seq1->toArray());
        $this->assertTrue($seq1->valueTypes->containsOnly('int'));
        $this->assertSame(['a'], $seq2->toArray());
    }

    #endregion

    #region Method reverse() tests.

    /**
     * Test reverse method.
     */
    public function testReverse(): void
    {
        // Test: Reverse a sequence
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $reversed = $seq->reverse();

        // Test: Verify order is reversed
        $this->assertSame(5, $reversed[0]);
        $this->assertSame(1, $reversed[4]);
        // Test: Original unchanged
        $this->assertSame(1, $seq[0]);
    }

    #endregion
}
