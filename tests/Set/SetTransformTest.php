<?php

declare(strict_types=1);

namespace Galaxon\Collections\Tests\Set;

use Galaxon\Collections\Set;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Tests for Set transformation methods.
 */
#[CoversClass(Set::class)]
class SetTransformTest extends TestCase
{
    /**
     * Test filter keeps items that pass test.
     */
    public function testFilterKeepsMatchingItems(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3, 4, 5, 6);

        $filtered = $set->filter(fn($value) => $value % 2 === 0);

        $this->assertCount(3, $filtered);
        $this->assertTrue($filtered->contains(2));
        $this->assertTrue($filtered->contains(4));
        $this->assertTrue($filtered->contains(6));
        $this->assertFalse($filtered->contains(1));
    }

    /**
     * Test filter returns empty set when no items match.
     */
    public function testFilterReturnsEmptyWhenNoMatches(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $filtered = $set->filter(fn($value) => $value > 10);

        $this->assertCount(0, $filtered);
        $this->assertTrue($filtered->empty());
    }

    /**
     * Test filter on empty set.
     */
    public function testFilterOnEmptySet(): void
    {
        $set = new Set('int');

        $filtered = $set->filter(fn($value) => true);

        $this->assertCount(0, $filtered);
    }

    /**
     * Test filter preserves type constraints.
     */
    public function testFilterPreservesTypeConstraints(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3, 4, 5);

        $filtered = $set->filter(fn($value) => $value > 2);

        // Test type constraints are preserved - we can add items with same type.
        $filtered->add(10);
        $this->assertCount(4, $filtered);
    }

    /**
     * Test filter callback must return bool.
     */
    public function testFilterCallbackMustReturnBool(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        // Test callback returning non-bool throws TypeError.
        $this->expectException(TypeError::class);
        $set->filter(fn($value) => $value); // Returns int, not bool
    }

    /**
     * Test filter keeps all items when callback always returns true.
     */
    public function testFilterKeepsAllWithAlwaysTrue(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3, 4, 5);

        $filtered = $set->filter(fn($value) => true);

        $this->assertCount(5, $filtered);
        $this->assertTrue($filtered->contains(1));
        $this->assertTrue($filtered->contains(5));
    }

    /**
     * Test filter is non-mutating.
     */
    public function testFilterIsNonMutating(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3, 4, 5);

        $filtered = $set->filter(fn($value) => $value % 2 === 0);

        // Test original set is unchanged.
        $this->assertCount(5, $set);
        $this->assertTrue($set->contains(1));

        // Test filtered set has expected items (2, 4).
        $this->assertCount(2, $filtered);
    }

    /**
     * Test filter with complex callback.
     */
    public function testFilterWithComplexCallback(): void
    {
        $set = new Set('string');
        $set->add('apple', 'banana', 'avocado', 'cherry');

        $filtered = $set->filter(fn($value) => str_starts_with($value, 'a'));

        $this->assertCount(2, $filtered);
        $this->assertTrue($filtered->contains('apple'));
        $this->assertTrue($filtered->contains('avocado'));
        $this->assertFalse($filtered->contains('banana'));
    }

    /**
     * Test filter with mixed types.
     */
    public function testFilterWithMixedTypes(): void
    {
        $set = new Set('int|string');
        $set->add(1, 'two', 3, 'four', 5);

        $filtered = $set->filter(fn($value) => is_int($value));

        $this->assertCount(3, $filtered);
        $this->assertTrue($filtered->contains(1));
        $this->assertTrue($filtered->contains(3));
        $this->assertTrue($filtered->contains(5));
        $this->assertFalse($filtered->contains('two'));
    }

    /**
     * Test all returns true when all items pass test.
     */
    public function testAllReturnsTrueWhenAllPass(): void
    {
        $set = new Set('int');
        $set->add(2, 4, 6, 8);

        $result = $set->all(fn($value) => $value % 2 === 0);

        $this->assertTrue($result);
    }

    /**
     * Test all returns false when any item fails test.
     */
    public function testAllReturnsFalseWhenAnyFail(): void
    {
        $set = new Set('int');
        $set->add(2, 4, 5, 8);

        $result = $set->all(fn($value) => $value % 2 === 0);

        $this->assertFalse($result);
    }

    /**
     * Test all returns true for empty set.
     */
    public function testAllReturnsTrueForEmptySet(): void
    {
        $set = new Set('int');

        $result = $set->all(fn($value) => $value > 100);

        $this->assertTrue($result);
    }

    /**
     * Test any returns true when at least one item passes test.
     */
    public function testAnyReturnsTrueWhenAnyPass(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3, 4, 5);

        $result = $set->any(fn($value) => $value > 3);

        $this->assertTrue($result);
    }

    /**
     * Test any returns false when no items pass test.
     */
    public function testAnyReturnsFalseWhenNonePass(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $result = $set->any(fn($value) => $value > 10);

        $this->assertFalse($result);
    }

    /**
     * Test any returns false for empty set.
     */
    public function testAnyReturnsFalseForEmptySet(): void
    {
        $set = new Set('int');

        $result = $set->any(fn($value) => true);

        $this->assertFalse($result);
    }
}
