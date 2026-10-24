<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Sequence;

use Mossy2100\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Sequence iteration.
 */
#[CoversClass(Sequence::class)]
class SequenceIteratorAggregateTest extends TestCase
{
    #region Method getIterator() tests.

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

    #endregion
}
