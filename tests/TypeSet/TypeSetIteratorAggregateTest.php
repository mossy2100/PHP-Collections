<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\TypeSet;

use Mossy2100\Collections\TypeSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for TypeSet iteration.
 */
#[CoversClass(TypeSet::class)]
class TypeSetIteratorAggregateTest extends TestCase
{
    #region Method getIterator() tests.

    /**
     * Test getIterator() method.
     */
    public function testGetIterator(): void
    {
        $ts = new TypeSet('int|string|bool');

        $types = [];
        foreach ($ts as $type) {
            $types[] = $type;
        }

        $this->assertCount(3, $types);
        $this->assertContains('int', $types);
        $this->assertContains('string', $types);
        $this->assertContains('bool', $types);
    }

    #endregion
}
