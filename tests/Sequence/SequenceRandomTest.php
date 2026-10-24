<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Sequence;

use DomainException;
use LengthException;
use Mossy2100\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Sequence random methods.
 */
#[CoversClass(Sequence::class)]
class SequenceRandomTest extends TestCase
{
    #region Method chooseRand() tests.

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
     * Test chooseRand returns the items in random order, not index order.
     */
    public function testChooseRandReturnsRandomOrder(): void
    {
        $seq = new Sequence('int', range(1, 10));

        // Over 100 draws of 5 items, at least one should not be ascending (each is with probability 1/120).
        $allAscending = true;
        for ($i = 0; $i < 100; $i++) {
            $chosen = $seq->chooseRand(5);
            $sorted = $chosen;
            sort($sorted);
            if ($chosen !== $sorted) {
                $allAscending = false;
                break;
            }
        }
        $this->assertFalse($allAscending);
    }

    /**
     * Test chooseRand with the full count returns every item.
     */
    public function testChooseRandAllItems(): void
    {
        $seq = new Sequence('int', range(1, 10));

        $chosen = $seq->chooseRand(10);
        sort($chosen);
        $this->assertSame(range(1, 10), $chosen);
    }

    #endregion

    #region Method removeRand() tests.

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
     * Test removeRand returns the items in random order, and removes exactly those items.
     */
    public function testRemoveRandReturnsRandomOrder(): void
    {
        $allAscending = true;
        for ($i = 0; $i < 100; $i++) {
            $seq = new Sequence('int', range(1, 10));
            $removed = $seq->removeRand(5);

            // Test the removed and remaining items together make up the original items.
            $all = array_merge($removed, $seq->toArray());
            sort($all);
            $this->assertSame(range(1, 10), $all);

            $sorted = $removed;
            sort($sorted);
            if ($removed !== $sorted) {
                $allAscending = false;
            }
        }
        $this->assertFalse($allAscending);
    }

    #endregion
}
