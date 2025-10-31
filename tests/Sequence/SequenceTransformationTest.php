<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Sequence;

use Galaxon\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Sequence transformation and utility methods.
 */
#[CoversClass(Sequence::class)]
class SequenceTransformationTest extends TestCase
{
    /**
     * Test sort method in ascending order.
     */
    public function testSortAscending(): void
    {
        // Test: Sort integers in ascending order
        $seq = new Sequence('int');
        $seq->append(5, 2, 8, 1, 9, 3);
        $sorted = $seq->sort();
        
        // Test: Verify items are sorted
        $this->assertSame(1, $sorted[0]);
        $this->assertSame(9, $sorted[5]);
        // Test: Original sequence unchanged (non-mutating)
        $this->assertSame(5, $seq[0]);
    }

    /**
     * Test sort method with strings.
     */
    public function testSortStrings(): void
    {
        // Test: Sort strings alphabetically
        $seq = new Sequence('string');
        $seq->append('zebra', 'apple', 'mango', 'banana');
        $sorted = $seq->sort();
        
        // Test: Verify alphabetical order
        $this->assertSame('apple', $sorted[0]);
        $this->assertSame('zebra', $sorted[3]);
    }

    /**
     * Test sortReverse method.
     */
    public function testSortReverse(): void
    {
        // Test: Sort in descending order
        $seq = new Sequence('int');
        $seq->append(5, 2, 8, 1, 9, 3);
        $sorted = $seq->sortReverse();
        
        // Test: Verify descending order
        $this->assertSame(9, $sorted[0]);
        $this->assertSame(1, $sorted[5]);
    }

    /**
     * Test sortBy method with custom comparison.
     */
    public function testSortByCustomComparison(): void
    {
        // Test: Sort by absolute value
        $seq = new Sequence('int');
        $seq->append(-5, 3, -1, 4, -2);
        $sorted = $seq->sortBy(fn($a, $b) => abs($a) <=> abs($b));
        
        // Test: Verify sorted by absolute value
        $this->assertSame(-1, $sorted[0]);
        $this->assertSame(-5, $sorted[4]);
    }

    /**
     * Test filter method keeps matching items.
     */
    public function testFilterKeepsMatchingItems(): void
    {
        // Test: Filter even numbers
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5, 6, 7, 8);
        $filtered = $seq->filter(fn($x) => $x % 2 === 0);
        
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
        $filtered = $seq->filter(fn($x) => $x > 10);
        
        // Test: Verify empty Sequence returned
        $this->assertCount(0, $filtered);
    }

    /**
     * Test map method transforms items.
     */
    public function testMapTransformsItems(): void
    {
        // Test: Double each value
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $mapped = $seq->map(fn($x) => $x * 2);
        
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
        $mapped = $seq->map(fn($x) => "Number $x");
        
        // Test: Verify type changed
        $this->assertSame('Number 1', $mapped[0]);
        $this->assertSame('Number 3', $mapped[2]);
    }

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
     * Test fill method with default value.
     */
    public function testFillWithDefaultValue(): void
    {
        // Test: Fill with default value
        $seq = new Sequence('int', 0);
        $seq->fill(0, 5);
        
        // Test: Verify filled with defaults
        $this->assertCount(5, $seq);
        $this->assertSame(0, $seq[0]);
        $this->assertSame(0, $seq[4]);
    }

    /**
     * Test reduce method aggregates values.
     */
    public function testReduceAggregatesValues(): void
    {
        // Test: Sum using reduce
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $sum = $seq->reduce(fn($acc, $x) => $acc + $x, 0);
        
        // Test: Verify correct sum
        $this->assertSame(15, $sum);
    }

    /**
     * Test reduce with different operation.
     */
    public function testReduceWithConcatenation(): void
    {
        // Test: Concatenate strings
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'c');
        $result = $seq->reduce(fn($acc, $x) => $acc . $x, '');
        
        // Test: Verify concatenation
        $this->assertSame('abc', $result);
    }

    /**
     * Test product method.
     */
    public function testProduct(): void
    {
        // Test: Calculate product
        $seq = new Sequence('int');
        $seq->append(2, 3, 4);
        
        // Test: Verify product calculation
        $this->assertSame(24, $seq->product());
    }

    /**
     * Test sum method.
     */
    public function testSum(): void
    {
        // Test: Calculate sum
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        
        // Test: Verify sum calculation
        $this->assertSame(15, $seq->sum());
    }

    /**
     * Test join method without glue.
     */
    public function testJoinWithoutGlue(): void
    {
        // Test: Join strings without separator
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'c');
        
        // Test: Verify joined string
        $this->assertSame('abc', $seq->join());
    }

    /**
     * Test join method with glue.
     */
    public function testJoinWithGlue(): void
    {
        // Test: Join with separator
        $seq = new Sequence('string');
        $seq->append('apple', 'banana', 'cherry');
        
        // Test: Verify joined with separator
        $this->assertSame('apple, banana, cherry', $seq->join(', '));
    }
}
