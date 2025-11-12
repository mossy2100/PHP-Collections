<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Set;

use Galaxon\Collections\Set;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Tests for Set add and remove methods.
 */
#[CoversClass(Set::class)]
class SetAddRemoveTest extends TestCase
{
    /**
     * Test add with single item.
     */
    public function testAddSingleItem(): void
    {
        $set = new Set('int');
        $result = $set->add(1);

        $this->assertSame($set, $result);
        $this->assertCount(1, $set);
        $this->assertTrue($set->contains(1));
    }

    /**
     * Test add with multiple items.
     */
    public function testAddMultipleItems(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3, 4, 5);

        $this->assertCount(5, $set);
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains(5));
    }

    /**
     * Test add ignores duplicate items.
     */
    public function testAddIgnoresDuplicates(): void
    {
        $set = new Set('int');
        $set->add(1);
        $set->add(1);
        $set->add(1);

        $this->assertCount(1, $set);
        $this->assertTrue($set->contains(1));
    }

    /**
     * Test add with invalid type throws TypeError.
     */
    public function testAddWithInvalidType(): void
    {
        $set = new Set('int');

        $this->expectException(TypeError::class);
        $set->add('apple');
    }

    /**
     * Test add supports chaining.
     */
    public function testAddChaining(): void
    {
        $set = new Set('int');
        $result = $set->add(1)->add(2)->add(3);

        $this->assertSame($set, $result);
        $this->assertCount(3, $set);
    }

    /**
     * Test add with mixed valid types.
     */
    public function testAddWithMixedTypes(): void
    {
        $set = new Set('int|string');
        $set->add(1, 'hello', 2, 'world');

        $this->assertCount(4, $set);
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains('hello'));
    }

    /**
     * Test add with spread operator.
     */
    public function testAddWithSpreadOperator(): void
    {
        $set = new Set('int');
        $items = [1, 2, 3, 4, 5];
        $set->add(...$items);

        $this->assertCount(5, $set);
    }

    /**
     * Test import from array.
     */
    public function testImportFromArray(): void
    {
        $set = new Set('int');
        $result = $set->import([1, 2, 3, 4, 5]);

        $this->assertSame($set, $result);
        $this->assertCount(5, $set);
    }

    /**
     * Test import removes duplicates.
     */
    public function testImportRemovesDuplicates(): void
    {
        $set = new Set('int');
        $set->import([1, 2, 2, 3, 3, 3]);

        $this->assertCount(3, $set);
    }

    /**
     * Test import with generator.
     */
    public function testImportWithGenerator(): void
    {
        $set = new Set('int');
        $generator = function () {
            yield 1;
            yield 2;
            yield 3;
        };

        $set->import($generator());

        $this->assertCount(3, $set);
    }

    /**
     * Test import throws TypeError for invalid types.
     */
    public function testImportThrowsTypeError(): void
    {
        $set = new Set('int');

        $this->expectException(TypeError::class);
        $set->import([1, 'string', 3]);
    }

    /**
     * Test import supports chaining.
     */
    public function testImportChaining(): void
    {
        $set = new Set('int');
        $result = $set->import([1, 2])->import([3, 4]);

        $this->assertSame($set, $result);
        $this->assertCount(4, $set);
    }

    /**
     * Test import from another Set.
     */
    public function testImportFromSet(): void
    {
        $set1 = new Set('int');
        $set1->add(1, 2, 3);

        $set2 = new Set('int');
        $set2->import($set1);

        $this->assertCount(3, $set2);
        $this->assertTrue($set2->contains(1));
    }

    /**
     * Test remove existing item returns true.
     */
    public function testRemoveExistingItem(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $result = $set->remove(2);

        $this->assertTrue($result);
        $this->assertCount(2, $set);
        $this->assertFalse($set->contains(2));
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains(3));
    }

    /**
     * Test remove non-existent item returns false.
     */
    public function testRemoveNonExistentItem(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $result = $set->remove(99);

        $this->assertFalse($result);
        $this->assertCount(3, $set);
    }

    /**
     * Test remove on empty set returns false.
     */
    public function testRemoveOnEmptySet(): void
    {
        $set = new Set('int');

        $result = $set->remove(1);

        $this->assertFalse($result);
        $this->assertCount(0, $set);
    }

    /**
     * Test remove with different types.
     */
    public function testRemoveWithDifferentTypes(): void
    {
        $set = new Set();
        $set->add(1, 'hello', 3.14);

        $this->assertTrue($set->remove('hello'));
        $this->assertCount(2, $set);
        $this->assertFalse($set->contains('hello'));
    }

    /**
     * Test remove uses strict equality.
     */
    public function testRemoveUsesStrictEquality(): void
    {
        $set = new Set();
        $set->add(1, '1');

        $set->remove(1);

        $this->assertCount(1, $set);
        $this->assertTrue($set->contains('1'));
        $this->assertFalse($set->contains(1));
    }

    /**
     * Test clear removes all items.
     */
    public function testClearRemovesAllItems(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3, 4, 5);

        $result = $set->clear();

        $this->assertSame($set, $result);
        $this->assertCount(0, $set);
        $this->assertTrue($set->empty());
    }

    /**
     * Test clear on empty set.
     */
    public function testClearOnEmptySet(): void
    {
        $set = new Set();

        $set->clear();

        $this->assertCount(0, $set);
        $this->assertTrue($set->empty());
    }

    /**
     * Test clear supports chaining.
     */
    public function testClearChaining(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $result = $set->clear()->add(10);

        $this->assertSame($set, $result);
        $this->assertCount(1, $set);
        $this->assertTrue($set->contains(10));
    }
}
