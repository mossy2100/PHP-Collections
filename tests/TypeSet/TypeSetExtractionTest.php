<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\TypeSet;

use LogicException;
use Mossy2100\Collections\TypeSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for TypeSet extraction methods.
 */
#[CoversClass(TypeSet::class)]
class TypeSetExtractionTest extends TestCase
{
    #region Method getDefaultValue() tests.

    /**
     * Test getDefaultValue() for null.
     */
    public function testGetDefaultValueForNull(): void
    {
        $ts = new TypeSet('?int');

        $default = $ts->getDefaultValue();

        $this->assertNull($default);
    }

    /**
     * Test getDefaultValue() for bool.
     */
    public function testGetDefaultValueForBool(): void
    {
        $ts = new TypeSet('bool');

        $default = $ts->getDefaultValue();

        $this->assertFalse($default);
    }

    /**
     * Test getDefaultValue() for int.
     */
    public function testGetDefaultValueForInt(): void
    {
        $ts = new TypeSet('int');

        $default = $ts->getDefaultValue();

        $this->assertSame(0, $default);
    }

    /**
     * Test getDefaultValue() for number.
     */
    public function testGetDefaultValueForNumber(): void
    {
        $ts = new TypeSet('number');

        $default = $ts->getDefaultValue();

        $this->assertSame(0, $default);
    }

    /**
     * Test getDefaultValue() for scalar.
     */
    public function testGetDefaultValueForScalar(): void
    {
        $ts = new TypeSet('scalar');

        $default = $ts->getDefaultValue();

        $this->assertSame(0, $default);
    }

    /**
     * Test getDefaultValue() for float.
     */
    public function testGetDefaultValueForFloat(): void
    {
        $ts = new TypeSet('float');

        $default = $ts->getDefaultValue();

        $this->assertSame(0.0, $default);
    }

    /**
     * Test getDefaultValue() for string.
     */
    public function testGetDefaultValueForString(): void
    {
        $ts = new TypeSet('string');

        $default = $ts->getDefaultValue();

        $this->assertSame('', $default);
    }

    /**
     * Test getDefaultValue() for array.
     */
    public function testGetDefaultValueForArray(): void
    {
        $ts = new TypeSet('array');

        $default = $ts->getDefaultValue();

        $this->assertSame([], $default);
    }

    /**
     * Test getDefaultValue() for iterable.
     */
    public function testGetDefaultValueForIterable(): void
    {
        $ts = new TypeSet('iterable');

        $default = $ts->getDefaultValue();

        $this->assertSame([], $default);
    }

    /**
     * Test getDefaultValue() for object.
     */
    public function testGetDefaultValueForObject(): void
    {
        $ts = new TypeSet('object');

        $default = $ts->getDefaultValue();

        $this->assertInstanceOf(stdClass::class, $default);
    }

    /**
     * Test getDefaultValue() throws LogicException for types without defaults.
     */
    public function testGetDefaultValueThrowsForDateTime(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot determine a default value for this TypeSet');

        $ts = new TypeSet('DateTime');
        $ts->getDefaultValue();
    }

    /**
     * Test getDefaultValue() throws LogicException for callable.
     */
    public function testGetDefaultValueThrowsForCallable(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot determine a default value for this TypeSet');

        $ts = new TypeSet('callable');
        $ts->getDefaultValue();
    }

    /**
     * Test getDefaultValue() priority: null has highest priority.
     */
    public function testGetDefaultValuePriorityNull(): void
    {
        $ts = new TypeSet('int|string|null');

        $default = $ts->getDefaultValue();

        $this->assertNull($default); // null has priority
    }

    /**
     * Test getDefaultValue() priority: bool before int.
     */
    public function testGetDefaultValuePriorityBool(): void
    {
        $ts = new TypeSet('int|bool|string');

        $default = $ts->getDefaultValue();

        $this->assertFalse($default); // bool has priority over int
    }

    #endregion
}
