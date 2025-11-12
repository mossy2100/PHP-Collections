<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Sequence;

use Galaxon\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;
use UnderflowException;

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
        // Test: Fill with value.
        $seq = new Sequence('int');
        $seq->fill(0, 5, 99);

        // Test: Verify filled with specified value.
        $this->assertCount(5, $seq);
        $this->assertSame(99, $seq[0]);
        $this->assertSame(99, $seq[4]);
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
     * Test product method with integers.
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
     * Test product method with floats.
     */
    public function testProductWithFloats(): void
    {
        // Test: Calculate product with floats
        $seq = new Sequence('float');
        $seq->append(2.5, 4.0, 2.0);

        // Test: Verify product calculation
        $this->assertSame(20.0, $seq->product());
    }

    /**
     * Test product method with mixed int and float.
     */
    public function testProductWithMixedNumericTypes(): void
    {
        // Test: Calculate product with mixed types
        $seq = new Sequence('int|float');
        $seq->append(2, 3.5, 2);

        // Test: Verify product calculation (result is float)
        $this->assertSame(14.0, $seq->product());
    }

    /**
     * Test product method with empty sequence returns multiplicative identity.
     */
    public function testProductWithEmptySequence(): void
    {
        // Test: Calculate product of empty sequence
        $seq = new Sequence('int');

        // Test: Verify returns 1 (multiplicative identity)
        $this->assertSame(1, $seq->product());
    }

    /**
     * Test product method with single value.
     */
    public function testProductWithSingleValue(): void
    {
        // Test: Calculate product with one value
        $seq = new Sequence('int');
        $seq->append(42);

        // Test: Verify returns the value itself
        $this->assertSame(42, $seq->product());
    }

    /**
     * Test product method throws TypeError for non-numeric values.
     */
    public function testProductThrowsTypeErrorForNonNumericValues(): void
    {
        // Test: Attempt to calculate product with strings
        $seq = new Sequence('string');
        $seq->append('1', '2', '3');

        $this->expectException(TypeError::class);
        $seq->product();
    }

    /**
     * Test product method with negative numbers.
     */
    public function testProductWithNegativeNumbers(): void
    {
        // Test: Calculate product with negative numbers
        $seq = new Sequence('int');
        $seq->append(-2, 3, -4);

        // Test: Verify product calculation
        $this->assertSame(24, $seq->product());
    }

    /**
     * Test sum method with integers.
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
     * Test sum method with floats.
     */
    public function testSumWithFloats(): void
    {
        // Test: Calculate sum with floats
        $seq = new Sequence('float');
        $seq->append(1.5, 2.5, 3.5);

        // Test: Verify sum calculation
        $this->assertSame(7.5, $seq->sum());
    }

    /**
     * Test sum method with mixed int and float.
     */
    public function testSumWithMixedNumericTypes(): void
    {
        // Test: Calculate sum with mixed types
        $seq = new Sequence('int|float');
        $seq->append(1, 2.5, 3);

        // Test: Verify sum calculation (result is float)
        $this->assertSame(6.5, $seq->sum());
    }

    /**
     * Test sum method with empty sequence returns additive identity.
     */
    public function testSumWithEmptySequence(): void
    {
        // Test: Calculate sum of empty sequence
        $seq = new Sequence('int');

        // Test: Verify returns 0 (additive identity)
        $this->assertSame(0, $seq->sum());
    }

    /**
     * Test sum method with single value.
     */
    public function testSumWithSingleValue(): void
    {
        // Test: Calculate sum with one value
        $seq = new Sequence('int');
        $seq->append(42);

        // Test: Verify returns the value itself
        $this->assertSame(42, $seq->sum());
    }

    /**
     * Test sum method throws TypeError for non-numeric values.
     */
    public function testSumThrowsTypeErrorForNonNumericValues(): void
    {
        // Test: Attempt to calculate sum with strings
        $seq = new Sequence('string');
        $seq->append('1', '2', '3');

        $this->expectException(TypeError::class);
        $seq->sum();
    }

    /**
     * Test sum method with negative numbers.
     */
    public function testSumWithNegativeNumbers(): void
    {
        // Test: Calculate sum with negative numbers
        $seq = new Sequence('int');
        $seq->append(-5, 3, -2, 8);

        // Test: Verify sum calculation
        $this->assertSame(4, $seq->sum());
    }

    /**
     * Test sum method with zero.
     */
    public function testSumWithZero(): void
    {
        // Test: Calculate sum with zeros
        $seq = new Sequence('int');
        $seq->append(0, 5, 0, 3);

        // Test: Verify sum calculation
        $this->assertSame(8, $seq->sum());
    }

    /**
     * Test min method.
     */
    public function testMin(): void
    {
        // Test: Find minimum value
        $seq = new Sequence('int');
        $seq->append(5, 2, 8, 1, 9, 3);

        // Test: Verify minimum value
        $this->assertSame(1, $seq->min());
    }

    /**
     * Test min method with floats.
     */
    public function testMinWithFloats(): void
    {
        // Test: Find minimum float value
        $seq = new Sequence('float');
        $seq->append(3.14, 2.71, 1.41, 4.67);

        // Test: Verify minimum float value
        $this->assertSame(1.41, $seq->min());
    }

    /**
     * Test min method with negative numbers.
     */
    public function testMinWithNegativeNumbers(): void
    {
        // Test: Find minimum with negative values
        $seq = new Sequence('int');
        $seq->append(5, -2, 8, -10, 3);

        // Test: Verify minimum negative value
        $this->assertSame(-10, $seq->min());
    }

    /**
     * Test min method throws UnderflowException on empty Sequence.
     */
    public function testMinThrowsUnderflowOnEmpty(): void
    {
        // Test: Attempt to find min of empty Sequence
        $this->expectException(UnderflowException::class);
        $this->expectExceptionMessage("Cannot find the minimum value of empty Sequence.");

        $seq = new Sequence('int');
        $seq->min();
    }

    /**
     * Test max method.
     */
    public function testMax(): void
    {
        // Test: Find maximum value
        $seq = new Sequence('int');
        $seq->append(5, 2, 8, 1, 9, 3);

        // Test: Verify maximum value
        $this->assertSame(9, $seq->max());
    }

    /**
     * Test max method with floats.
     */
    public function testMaxWithFloats(): void
    {
        // Test: Find maximum float value
        $seq = new Sequence('float');
        $seq->append(3.14, 2.71, 1.41, 4.67);

        // Test: Verify maximum float value
        $this->assertSame(4.67, $seq->max());
    }

    /**
     * Test max method with negative numbers.
     */
    public function testMaxWithNegativeNumbers(): void
    {
        // Test: Find maximum with negative values
        $seq = new Sequence('int');
        $seq->append(-5, -2, -8, -1, -3);

        // Test: Verify maximum negative value
        $this->assertSame(-1, $seq->max());
    }

    /**
     * Test max method throws UnderflowException on empty Sequence.
     */
    public function testMaxThrowsUnderflowOnEmpty(): void
    {
        // Test: Attempt to find max of empty Sequence
        $this->expectException(UnderflowException::class);
        $this->expectExceptionMessage("Cannot find the maximum value of empty Sequence.");

        $seq = new Sequence('int');
        $seq->max();
    }

    /**
     * Test average method.
     */
    public function testAverage(): void
    {
        // Test: Calculate average
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);

        // Test: Verify average calculation
        $this->assertSame(3, $seq->average());
    }

    /**
     * Test average method with floats.
     */
    public function testAverageWithFloats(): void
    {
        // Test: Calculate average of floats
        $seq = new Sequence('float');
        $seq->append(1.5, 2.5, 3.5, 4.5);

        // Test: Verify average calculation
        $this->assertSame(3.0, $seq->average());
    }

    /**
     * Test average method returns float for division.
     */
    public function testAverageReturnsFloat(): void
    {
        // Test: Calculate average that results in float
        $seq = new Sequence('int');
        $seq->append(1, 2, 3);

        // Test: Verify average is 2.0 (float)
        $this->assertEquals(2.0, $seq->average());
    }

    /**
     * Test average method with single item.
     */
    public function testAverageWithSingleItem(): void
    {
        // Test: Calculate average of single item
        $seq = new Sequence('int');
        $seq->append(42);

        // Test: Verify average equals the single item
        $this->assertSame(42, $seq->average());
    }

    /**
     * Test average method throws UnderflowException on empty Sequence.
     */
    public function testAverageThrowsUnderflowOnEmpty(): void
    {
        // Test: Attempt to find average of empty Sequence
        $this->expectException(UnderflowException::class);
        $this->expectExceptionMessage("Cannot calculate the average value of empty Sequence.");

        $seq = new Sequence('int');
        $seq->average();
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
