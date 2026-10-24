<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\TypeSet;

use Mossy2100\Collections\TypeSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for TypeSet conversion methods.
 */
#[CoversClass(TypeSet::class)]
class TypeSetConversionTest extends TestCase
{
    #region Method __toString() tests.

    /**
     * Test __toString() method.
     */
    public function testToString(): void
    {
        $ts1 = new TypeSet();
        $this->assertSame('{}', (string) $ts1);

        $ts2 = new TypeSet('int');
        $this->assertSame('{int}', (string) $ts2);

        $ts3 = new TypeSet('int|string');
        $str = (string) $ts3;
        // Order might vary, so check both possibilities
        $this->assertTrue($str === '{int, string}' || $str === '{string, int}');
    }

    #endregion
}
