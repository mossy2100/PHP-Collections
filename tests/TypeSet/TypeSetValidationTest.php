<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\TypeSet;

use InvalidArgumentException;
use Mossy2100\Collections\TypeSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for TypeSet validation methods.
 */
#[CoversClass(TypeSet::class)]
class TypeSetValidationTest extends TestCase
{
    #region Method checkValueType() tests.

    /**
     * Test check() passes for valid type.
     */
    public function testCheckPassesForValidType(): void
    {
        $this->expectNotToPerformAssertions();

        // Should not throw
        $ts = new TypeSet('int');
        $ts->checkValueType(42);
    }

    /**
     * Test check() throws InvalidArgumentException for invalid type.
     */
    public function testCheckThrowsInvalidArgumentExceptionForInvalidType(): void
    {
        $ts = new TypeSet('int');

        $this->expectException(InvalidArgumentException::class);
        $ts->checkValueType('hello');
    }

    /**
     * Test check() with custom label.
     */
    public function testCheckWithCustomLabel(): void
    {
        $ts = new TypeSet('int');

        try {
            $ts->checkValueType('hello', 'value');
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('value', $e->getMessage());
        }
    }

    #endregion
}
