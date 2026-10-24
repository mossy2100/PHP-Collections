<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Sequence;

use Mossy2100\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Sequence sorting methods.
 */
#[CoversClass(Sequence::class)]
class SequenceSortingTest extends TestCase
{
    #region Method sort() tests.

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

    #endregion

    #region Method sortReverse() tests.

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

    #endregion

    #region Method sortBy() tests.

    /**
     * Test sortBy method with custom comparison.
     */
    public function testSortByCustomComparison(): void
    {
        // Test: Sort by absolute value
        $seq = new Sequence('int');
        $seq->append(-5, 3, -1, 4, -2);
        $sorted = $seq->sortBy(static fn ($a, $b) => abs($a) <=> abs($b));

        // Test: Verify sorted by absolute value
        $this->assertSame(-1, $sorted[0]);
        $this->assertSame(-5, $sorted[4]);
    }

    #endregion
}
