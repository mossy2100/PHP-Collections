<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\TypeSet;

use Mossy2100\Collections\TypeSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for TypeSet set operations.
 */
#[CoversClass(TypeSet::class)]
class TypeSetOperationsTest extends TestCase
{
    #region Method union() tests.

    /**
     * Test union combines the types from both TypeSets.
     */
    public function testUnionCombinesTypes(): void
    {
        $result = new TypeSet('int|string')->union(new TypeSet('string|float'));

        $this->assertTrue($result->containsOnly('int', 'string', 'float'));
    }

    /**
     * Test union with an unconstrained TypeSet on either side allows any type.
     */
    public function testUnionWithUnconstrainedTypeSet(): void
    {
        $typed = new TypeSet('int');
        $any = new TypeSet();

        $this->assertTrue($typed->union($any)->anyOk());
        $this->assertTrue($any->union($typed)->anyOk());
    }

    /**
     * Test union with mixed on either side allows any type.
     */
    public function testUnionWithMixed(): void
    {
        $typed = new TypeSet('int');
        $mixed = new TypeSet('mixed');

        $this->assertTrue($typed->union($mixed)->match('text'));
        $this->assertTrue($mixed->union($typed)->match('text'));
    }

    /**
     * Test union doesn't modify either TypeSet.
     */
    public function testUnionIsNonMutating(): void
    {
        $a = new TypeSet('int');
        $b = new TypeSet('string');

        $result = $a->union($b);

        $this->assertNotSame($a, $result);
        $this->assertTrue($a->containsOnly('int'));
        $this->assertTrue($b->containsOnly('string'));
    }

    #endregion
}
