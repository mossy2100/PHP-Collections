<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Set;

use Mossy2100\Collections\Collection;
use Mossy2100\Collections\Set;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Set inspection methods.
 */
#[CoversClass(Set::class)]
#[CoversClass(Collection::class)]
class SetInspectionTest extends TestCase
{
    #region Method contains() tests.

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

    #endregion

    #region Method empty() tests.

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

    #endregion

    #region Method all() tests.

    /**
     * Test all returns true when all items pass test.
     */
    public function testAllReturnsTrueWhenAllPass(): void
    {
        $set = new Set('int');
        $set->add(2, 4, 6, 8);

        $result = $set->all(static fn ($value) => $value % 2 === 0);

        $this->assertTrue($result);
    }

    /**
     * Test all returns false when any item fails test.
     */
    public function testAllReturnsFalseWhenAnyFail(): void
    {
        $set = new Set('int');
        $set->add(2, 4, 5, 8);

        $result = $set->all(static fn ($value) => $value % 2 === 0);

        $this->assertFalse($result);
    }

    /**
     * Test all returns true for empty set.
     */
    public function testAllReturnsTrueForEmptySet(): void
    {
        $set = new Set('int');

        $result = $set->all(static fn ($value) => $value > 100);

        $this->assertTrue($result);
    }

    #endregion

    #region Method any() tests.

    /**
     * Test any returns true when at least one item passes test.
     */
    public function testAnyReturnsTrueWhenAnyPass(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3, 4, 5);

        $result = $set->any(static fn ($value) => $value > 3);

        $this->assertTrue($result);
    }

    /**
     * Test any returns false when no items pass test.
     */
    public function testAnyReturnsFalseWhenNonePass(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $result = $set->any(static fn ($value) => $value > 10);

        $this->assertFalse($result);
    }

    /**
     * Test any returns false for empty set.
     */
    public function testAnyReturnsFalseForEmptySet(): void
    {
        $set = new Set('int');

        $result = $set->any(static fn ($value) => true);

        $this->assertFalse($result);
    }

    #endregion

    #region Method count() tests.

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

    /**
     * Test count interface works correctly.
     */
    public function testCountInterface(): void
    {
        $set = new Set('int');

        // Test count on empty set.
        $this->assertCount(0, $set);

        // Test count after adding items.
        $set->add(1);
        $this->assertCount(1, $set);

        $set->add(2, 3);
        $this->assertCount(3, $set);

        // Test count after removing items.
        $set->remove(1);
        $this->assertCount(2, $set);
    }

    #endregion
}
