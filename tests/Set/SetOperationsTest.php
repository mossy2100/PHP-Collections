<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Set;

use Galaxon\Collections\Set;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Set operations (union, intersect, diff).
 */
#[CoversClass(Set::class)]
class SetOperationsTest extends TestCase
{
    /**
     * Test union combines items from both sets.
     */
    public function testUnionCombinesSets(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(3, 4, 5);

        $result = $set1->union($set2);

        $this->assertCount(5, $result);
        $this->assertTrue($result->contains(1));
        $this->assertTrue($result->contains(2));
        $this->assertTrue($result->contains(3));
        $this->assertTrue($result->contains(4));
        $this->assertTrue($result->contains(5));
    }

    /**
     * Test union with no overlap.
     */
    public function testUnionWithNoOverlap(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2);

        $set2 = new Set('int');
        $set2->add(3, 4);

        $result = $set1->union($set2);

        $this->assertCount(4, $result);
    }

    /**
     * Test union with complete overlap.
     */
    public function testUnionWithCompleteOverlap(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(1, 2, 3);

        $result = $set1->union($set2);

        $this->assertCount(3, $result);
    }

    /**
     * Test union with empty set.
     */
    public function testUnionWithEmptySet(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');

        $result = $set1->union($set2);

        $this->assertCount(3, $result);
        $this->assertTrue($result->contains(1));
    }

    /**
     * Test union is non-mutating.
     */
    public function testUnionIsNonMutating(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2);

        $set2 = new Set('int');
        $set2->add(3, 4);

        $result = $set1->union($set2);

        $this->assertCount(2, $set1);
        $this->assertCount(2, $set2);
        $this->assertCount(4, $result);
    }

    /**
     * Test union combines type constraints.
     */
    public function testUnionCombinesTypeConstraints(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2);

        $set2 = new Set('string');
        $set2->add('a', 'b');

        $result = $set1->union($set2);

        $this->assertTrue($result->valueTypes->containsAll('int', 'string'));
    }

    /**
     * Test intersect returns common items.
     */
    public function testIntersectReturnsCommonItems(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3, 4);

        $set2 = new Set('int');
        $set2->add(3, 4, 5, 6);

        $result = $set1->intersect($set2);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains(3));
        $this->assertTrue($result->contains(4));
        $this->assertFalse($result->contains(1));
        $this->assertFalse($result->contains(5));
    }

    /**
     * Test intersect with no overlap returns empty set.
     */
    public function testIntersectWithNoOverlap(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2);

        $set2 = new Set('int');
        $set2->add(3, 4);

        $result = $set1->intersect($set2);

        $this->assertCount(0, $result);
        $this->assertTrue($result->empty());
    }

    /**
     * Test intersect with complete overlap.
     */
    public function testIntersectWithCompleteOverlap(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(1, 2, 3);

        $result = $set1->intersect($set2);

        $this->assertCount(3, $result);
    }

    /**
     * Test intersect with empty set.
     */
    public function testIntersectWithEmptySet(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');

        $result = $set1->intersect($set2);

        $this->assertCount(0, $result);
        $this->assertTrue($result->empty());
    }

    /**
     * Test intersect is non-mutating.
     */
    public function testIntersectIsNonMutating(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(2, 3, 4);

        $result = $set1->intersect($set2);

        $this->assertCount(3, $set1);
        $this->assertCount(3, $set2);
        $this->assertCount(2, $result);
    }

    /**
     * Test intersect preserves type constraints from first set.
     */
    public function testIntersectPreservesTypeConstraints(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2);

        $set2 = new Set('int|string');
        $set2->add(2, 3);

        $result = $set1->intersect($set2);

        $this->assertTrue($result->valueTypes->containsOnly('int'));
    }

    /**
     * Test diff returns items in first set but not in second.
     */
    public function testDiffReturnsUniqueItems(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3, 4);

        $set2 = new Set('int');
        $set2->add(3, 4, 5, 6);

        $result = $set1->diff($set2);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains(1));
        $this->assertTrue($result->contains(2));
        $this->assertFalse($result->contains(3));
        $this->assertFalse($result->contains(4));
    }

    /**
     * Test diff with no overlap returns original set.
     */
    public function testDiffWithNoOverlap(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2);

        $set2 = new Set('int');
        $set2->add(3, 4);

        $result = $set1->diff($set2);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains(1));
        $this->assertTrue($result->contains(2));
    }

    /**
     * Test diff with complete overlap returns empty set.
     */
    public function testDiffWithCompleteOverlap(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(1, 2, 3);

        $result = $set1->diff($set2);

        $this->assertCount(0, $result);
        $this->assertTrue($result->empty());
    }

    /**
     * Test diff with empty set returns original set.
     */
    public function testDiffWithEmptySet(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');

        $result = $set1->diff($set2);

        $this->assertCount(3, $result);
    }

    /**
     * Test diff is non-mutating.
     */
    public function testDiffIsNonMutating(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(2, 3, 4);

        $result = $set1->diff($set2);

        $this->assertCount(3, $set1);
        $this->assertCount(3, $set2);
        $this->assertCount(1, $result);
    }

    /**
     * Test diff preserves type constraints from first set.
     */
    public function testDiffPreservesTypeConstraints(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2);

        $set2 = new Set('int|string');
        $set2->add(2, 3);

        $result = $set1->diff($set2);

        $this->assertTrue($result->valueTypes->containsOnly('int'));
    }

    /**
     * Test diff is not commutative.
     */
    public function testDiffIsNotCommutative(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(2, 3, 4);

        $diff1 = $set1->diff($set2);
        $diff2 = $set2->diff($set1);

        $this->assertCount(1, $diff1);
        $this->assertTrue($diff1->contains(1));

        $this->assertCount(1, $diff2);
        $this->assertTrue($diff2->contains(4));
    }
}
