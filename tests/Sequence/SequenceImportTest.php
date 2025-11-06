<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Sequence;

use Galaxon\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Tests for Sequence import() method.
 */
#[CoversClass(Sequence::class)]
class SequenceImportTest extends TestCase
{
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
        $generator = function () {
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
     * Test import throws TypeError for invalid types.
     */
    public function testImportThrowsTypeErrorForInvalidTypes(): void
    {
        // Test: Attempt to import items with wrong type
        $this->expectException(TypeError::class);

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
     * Test import stops on first type error.
     */
    public function testImportStopsOnFirstTypeError(): void
    {
        // Test: Verify import stops at first invalid type
        $seq = new Sequence('int');
        $seq->append(1, 2);

        try {
            $seq->import([3, 4, 'invalid', 5, 6]);
            $this->fail('Expected TypeError was not thrown');
        } catch (TypeError $e) {
            // Test: Verify only valid items before error were imported
            $this->assertCount(4, $seq); // 1, 2, 3, 4
            $this->assertSame(4, $seq[3]);
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
        $seq->import(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry']);

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
}
