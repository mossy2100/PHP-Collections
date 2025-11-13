<?php

declare(strict_types=1);

namespace Galaxon\Collections\Tests\Set;

use Galaxon\Collections\Sequence;
use Galaxon\Collections\Set;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Set comparison and inspection methods.
 */
#[CoversClass(Set::class)]
class SetComparisonTest extends TestCase
{
    /**
     * Test contains returns true for existing item.
     */
    public function testContainsExistingItem(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $this->assertTrue($set->contains(2));
    }

    /**
     * Test contains returns false for non-existing item.
     */
    public function testContainsNonExistingItem(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $this->assertFalse($set->contains(99));
    }

    /**
     * Test contains uses strict equality.
     */
    public function testContainsUsesStrictEquality(): void
    {
        $set = new Set();
        $set->add(1, '1');

        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains('1'));
        $this->assertFalse($set->contains(2));
    }

    /**
     * Test contains on empty set.
     */
    public function testContainsOnEmptySet(): void
    {
        $set = new Set('int');

        $this->assertFalse($set->contains(1));
    }

    /**
     * Test equals returns true for equal sets.
     */
    public function testEqReturnsTrueForEqualSets(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(1, 2, 3);

        $this->assertTrue($set1->equals($set2));
        $this->assertTrue($set2->equals($set1));
    }

    /**
     * Test equals returns true regardless of insertion order.
     */
    public function testEqIgnoresOrder(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(3, 2, 1);

        $this->assertTrue($set1->equals($set2));
    }

    /**
     * Test equals returns false for sets with different sizes.
     */
    public function testEqReturnsFalseForDifferentSizes(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(1, 2);

        $this->assertFalse($set1->equals($set2));
    }

    /**
     * Test equals returns false for sets with different items.
     */
    public function testEqReturnsFalseForDifferentItems(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(1, 2, 4);

        $this->assertFalse($set1->equals($set2));
    }

    /**
     * Test equals returns false for different collection types.
     */
    public function testEqReturnsFalseForDifferentTypes(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $seq = new Sequence('int');
        $seq->append(1, 2, 3);

        $this->assertFalse($set->equals($seq));
    }

    /**
     * Test equals returns true for empty sets.
     */
    public function testEqForEmptySets(): void
    {
        $set1 = new Set('int');
        $set2 = new Set('int');

        $this->assertTrue($set1->equals($set2));
    }

    /**
     * Test equals ignores type constraints.
     */
    public function testEqIgnoresTypeConstraints(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int|string');
        $set2->add(1, 2, 3);

        $this->assertTrue($set1->equals($set2));
    }

    /**
     * Test isSubsetOf returns true when all items are in other set.
     */
    public function testIsSubsetOfReturnsTrueForSubset(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2);

        $set2 = new Set('int');
        $set2->add(1, 2, 3, 4);

        $this->assertTrue($set1->isSubsetOf($set2));
    }

    /**
     * Test isSubsetOf returns false when not all items are in other set.
     */
    public function testIsSubsetOfReturnsFalseForNonSubset(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 5);

        $set2 = new Set('int');
        $set2->add(1, 2, 3, 4);

        $this->assertFalse($set1->isSubsetOf($set2));
    }

    /**
     * Test isSubsetOf returns true for equal sets.
     */
    public function testIsSubsetOfReturnsTrueForEqualSets(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(1, 2, 3);

        $this->assertTrue($set1->isSubsetOf($set2));
    }

    /**
     * Test isSubsetOf returns true for empty set.
     */
    public function testIsSubsetOfReturnsTrueForEmptySet(): void
    {
        $set1 = new Set('int');

        $set2 = new Set('int');
        $set2->add(1, 2, 3);

        $this->assertTrue($set1->isSubsetOf($set2));
    }

    /**
     * Test isProperSubsetOf returns true for proper subset.
     */
    public function testIsProperSubsetOfReturnsTrueForProperSubset(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2);

        $set2 = new Set('int');
        $set2->add(1, 2, 3, 4);

        $this->assertTrue($set1->isProperSubsetOf($set2));
    }

    /**
     * Test isProperSubsetOf returns false for equal sets.
     */
    public function testIsProperSubsetOfReturnsFalseForEqualSets(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(1, 2, 3);

        $this->assertFalse($set1->isProperSubsetOf($set2));
    }

    /**
     * Test isProperSubsetOf returns false when not a subset.
     */
    public function testIsProperSubsetOfReturnsFalseForNonSubset(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 5);

        $set2 = new Set('int');
        $set2->add(1, 2, 3, 4);

        $this->assertFalse($set1->isProperSubsetOf($set2));
    }

    /**
     * Test isSupersetOf returns true when contains all items from other set.
     */
    public function testIsSupersetOfReturnsTrueForSuperset(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3, 4);

        $set2 = new Set('int');
        $set2->add(1, 2);

        $this->assertTrue($set1->isSupersetOf($set2));
    }

    /**
     * Test isSupersetOf returns false when not containing all items.
     */
    public function testIsSupersetOfReturnsFalseForNonSuperset(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(1, 2, 5);

        $this->assertFalse($set1->isSupersetOf($set2));
    }

    /**
     * Test isSupersetOf returns true for equal sets.
     */
    public function testIsSupersetOfReturnsTrueForEqualSets(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(1, 2, 3);

        $this->assertTrue($set1->isSupersetOf($set2));
    }

    /**
     * Test isProperSupersetOf returns true for proper superset.
     */
    public function testIsProperSupersetOfReturnsTrueForProperSuperset(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3, 4);

        $set2 = new Set('int');
        $set2->add(1, 2);

        $this->assertTrue($set1->isProperSupersetOf($set2));
    }

    /**
     * Test isProperSupersetOf returns false for equal sets.
     */
    public function testIsProperSupersetOfReturnsFalseForEqualSets(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(1, 2, 3);

        $this->assertFalse($set1->isProperSupersetOf($set2));
    }

    /**
     * Test isProperSupersetOf returns false when not a superset.
     */
    public function testIsProperSupersetOfReturnsFalseForNonSuperset(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(1, 2, 5);

        $this->assertFalse($set1->isProperSupersetOf($set2));
    }

    /**
     * Test isDisjointFrom returns true for disjoint sets.
     */
    public function testIsDisjointFromReturnsTrueForDisjointSets(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(4, 5, 6);

        $this->assertTrue($set1->isDisjointFrom($set2));
        $this->assertTrue($set2->isDisjointFrom($set1));
    }

    /**
     * Test isDisjointFrom returns false for overlapping sets.
     */
    public function testIsDisjointFromReturnsFalseForOverlappingSets(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->add(3, 4, 5);

        $this->assertFalse($set1->isDisjointFrom($set2));
    }

    /**
     * Test isDisjointFrom returns true for empty sets.
     */
    public function testIsDisjointFromReturnsTrueForEmptySets(): void
    {
        $set1 = new Set('int');
        $set2 = new Set('int');

        $this->assertTrue($set1->isDisjointFrom($set2));
    }

    /**
     * Test isDisjointFrom returns true when one set is empty.
     */
    public function testIsDisjointFromReturnsTrueWhenOneSetIsEmpty(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');

        $this->assertTrue($set1->isDisjointFrom($set2));
        $this->assertTrue($set2->isDisjointFrom($set1));
    }

    /**
     * Test empty returns true for empty set.
     */
    public function testEmptyReturnsTrueForEmptySet(): void
    {
        $set = new Set('int');

        $this->assertTrue($set->empty());
    }

    /**
     * Test empty returns false for non-empty set.
     */
    public function testEmptyReturnsFalseForNonEmptySet(): void
    {
        $set = new Set('int');
        $set->add(1);

        $this->assertFalse($set->empty());
    }

    /**
     * Test count returns correct number of items.
     */
    public function testCountReturnsCorrectCount(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3, 4, 5);

        $this->assertCount(5, $set);
        $this->assertEquals(5, $set->count());
    }

    /**
     * Test count returns zero for empty set.
     */
    public function testCountReturnsZeroForEmptySet(): void
    {
        $set = new Set('int');

        $this->assertCount(0, $set);
    }
}
