<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\TypeSet;

use DomainException;
use Mossy2100\Collections\TypeSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for TypeSet constructor.
 */
#[CoversClass(TypeSet::class)]
class TypeSetConstructorTest extends TestCase
{
    #region Method __construct() tests.

    /**
     * Test constructor with no arguments creates empty TypeSet.
     */
    public function testConstructorWithNoArguments(): void
    {
        $ts = new TypeSet();

        $this->assertInstanceOf(TypeSet::class, $ts);
        $this->assertCount(0, $ts);
        $this->assertTrue($ts->empty());
    }

    /**
     * Test constructor with single type string.
     */
    public function testConstructorWithSingleType(): void
    {
        $ts = new TypeSet('int');

        $this->assertCount(1, $ts);
        $this->assertTrue($ts->contains('int'));
    }

    /**
     * Test constructor with union type string.
     */
    public function testConstructorWithUnionType(): void
    {
        $ts = new TypeSet('int|string');

        $this->assertCount(2, $ts);
        $this->assertTrue($ts->contains('int'));
        $this->assertTrue($ts->contains('string'));
    }

    /**
     * Test constructor with nullable type string.
     */
    public function testConstructorWithNullableType(): void
    {
        $ts = new TypeSet('?int');

        $this->assertCount(2, $ts);
        $this->assertTrue($ts->contains('int'));
        $this->assertTrue($ts->contains('null'));
    }

    /**
     * Test constructor with array of types.
     */
    public function testConstructorWithArrayOfTypes(): void
    {
        $ts = new TypeSet(['int', 'string', 'bool']);

        $this->assertCount(3, $ts);
        $this->assertTrue($ts->containsAll('int', 'string', 'bool'));
    }

    /**
     * Test constructor with null argument.
     */
    public function testConstructorWithNull(): void
    {
        $ts = new TypeSet(null);

        $this->assertCount(0, $ts);
        $this->assertTrue($ts->empty());
    }

    /**
     * Test constructor with invalid type throws DomainException.
     */
    public function testConstructorWithInvalidTypeThrowsDomainException(): void
    {
        $this->expectException(DomainException::class);
        new TypeSet('invalid-type-name');
    }

    #endregion
}
