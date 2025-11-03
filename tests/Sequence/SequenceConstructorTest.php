<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Sequence;

use DateTime;
use Galaxon\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;
use ValueError;

/**
 * Tests for Sequence constructor and factory methods.
 */
#[CoversClass(Sequence::class)]
class SequenceConstructorTest extends TestCase
{
    /**
     * Test basic constructor with no type constraints.
     */
    public function testConstructorWithoutTypeConstraints(): void
    {
        // Test: Create a Sequence without type constraints
        $seq = new Sequence();

        $this->assertInstanceOf(Sequence::class, $seq);
        $this->assertCount(0, $seq);
    }

    /**
     * Test constructor with string type constraint.
     */
    public function testConstructorWithStringType(): void
    {
        // Test: Create a Sequence with string type constraint
        $seq = new Sequence('string');

        $this->assertInstanceOf(Sequence::class, $seq);
        // Test: Verify default value is empty string
        $this->assertSame('', $seq->defaultValue);
    }

    /**
     * Test constructor with int type constraint.
     */
    public function testConstructorWithIntType(): void
    {
        // Test: Create a Sequence with int type constraint
        $seq = new Sequence('int');

        // Test: Verify default value is 0
        $this->assertSame(0, $seq->defaultValue);
    }

    /**
     * Test constructor with float type constraint.
     */
    public function testConstructorWithFloatType(): void
    {
        // Test: Create a Sequence with float type constraint
        $seq = new Sequence('float');

        // Test: Verify default value is 0.0
        $this->assertSame(0.0, $seq->defaultValue);
    }

    /**
     * Test constructor with bool type constraint.
     */
    public function testConstructorWithBoolType(): void
    {
        // Test: Create a Sequence with bool type constraint
        $seq = new Sequence('bool');

        // Test: Verify default value is false
        $this->assertFalse($seq->defaultValue);
    }

    /**
     * Test constructor with array type constraint.
     */
    public function testConstructorWithArrayType(): void
    {
        // Test: Create a Sequence with array type constraint
        $seq = new Sequence('array');

        // Test: Verify default value is empty array
        $this->assertSame([], $seq->defaultValue);
    }

    /**
     * Test constructor with nullable type constraint.
     */
    public function testConstructorWithNullableType(): void
    {
        // Test: Create a Sequence with nullable int type
        $seq = new Sequence('?int');

        // Test: Verify default value is null
        $this->assertNull($seq->defaultValue);
    }

    /**
     * Test constructor with union type constraint.
     */
    public function testConstructorWithUnionType(): void
    {
        // Test: Create a Sequence with union type constraint
        $seq = new Sequence('string|int');

        // Test: Verify default value is determined (should be 0 for int)
        $this->assertSame(0, $seq->defaultValue);
    }

    /**
     * Test constructor with custom default value.
     */
    public function testConstructorWithCustomDefaultValue(): void
    {
        // Test: Create a Sequence with custom default value
        $seq = new Sequence('string', 'default');

        // Test: Verify custom default value
        $this->assertSame('default', $seq->defaultValue);
    }

    /**
     * Test constructor with object type and default value.
     */
    public function testConstructorWithObjectTypeAndDefault(): void
    {
        // Test: Create a Sequence with DateTime type and default
        $default = new DateTime('2025-01-01');
        $seq = new Sequence('DateTime', $default);

        // Test: Verify default value is set
        $this->assertInstanceOf(DateTime::class, $seq->defaultValue);
        $this->assertEquals($default, $seq->defaultValue);
    }

    /**
     * Test constructor throws ValueError when default cannot be determined.
     */
    public function testConstructorThrowsValueErrorForUndeterminedDefault(): void
    {
        // Test: Attempt to create a Sequence with object type but no default
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("A default value could not be determined");

        new Sequence('DateTime');
    }

    /**
     * Test constructor throws TypeError for invalid default value.
     */
    public function testConstructorThrowsTypeErrorForInvalidDefault(): void
    {
        // Test: Attempt to create a Sequence with mismatched default type
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("The default value has an invalid type");

        new Sequence('int', 'string_value');
    }

    /**
     * Test fromIterable factory method with array.
     */
    public function testFromIterableWithArray(): void
    {
        // Test: Create Sequence from array
        $source = [1, 2, 3, 4, 5];
        $seq = Sequence::fromIterable($source);

        // Test: Verify all items are copied
        $this->assertCount(5, $seq);
        $this->assertSame(1, $seq[0]);
        $this->assertSame(5, $seq[4]);
    }

    /**
     * Test fromIterable factory method with mixed types.
     */
    public function testFromIterableWithMixedTypes(): void
    {
        // Test: Create Sequence from mixed type array
        $source = [1, 'two', 3.0, true];
        $seq = Sequence::fromIterable($source);

        // Test: Verify all items and types are preserved
        $this->assertCount(4, $seq);
        $this->assertSame(1, $seq[0]);
        $this->assertSame('two', $seq[1]);
        $this->assertSame(3.0, $seq[2]);
        $this->assertTrue($seq[3]);
    }

    /**
     * Test fromIterable factory method with empty iterable.
     */
    public function testFromIterableWithEmptyArray(): void
    {
        // Test: Create Sequence from empty array
        $seq = Sequence::fromIterable([]);

        // Test: Verify Sequence is empty
        $this->assertCount(0, $seq);
    }

    /**
     * Test fromSubset method.
     */
    public function testFromSubset(): void
    {
        // Test: Create original Sequence
        $original = new Sequence('int', 0);
        $original->append(1, 2, 3, 4, 5);

        // Test: Create subset with different items
        $subset = $original->fromSubset([10, 20, 30]);

        // Test: Verify subset has same types and default but different items
        $this->assertCount(3, $subset);
        $this->assertSame(10, $subset[0]);
        $this->assertSame(30, $subset[2]);
        $this->assertSame(0, $subset->defaultValue);
    }

    /**
     * Test fromSubset with empty items.
     */
    public function testFromSubsetEmpty(): void
    {
        // Test: Create original Sequence
        $original = new Sequence('string', '');

        // Test: Create empty subset
        $subset = $original->fromSubset();

        // Test: Verify subset is empty but has same configuration
        $this->assertCount(0, $subset);
        $this->assertSame('', $subset->defaultValue);
    }

    /**
     * Test range method with ascending integers.
     */
    public function testRangeAscendingIntegers(): void
    {
        // Test: Create range from 1 to 5
        $seq = Sequence::range(1, 5);

        // Test: Verify range values
        $this->assertCount(5, $seq);
        $this->assertSame(1, $seq[0]);
        $this->assertSame(5, $seq[4]);
    }

    /**
     * Test range method with descending integers.
     */
    public function testRangeDescendingIntegers(): void
    {
        // Test: Create range from 10 to 1 with step -1
        $seq = Sequence::range(10, 1, -1);

        // Test: Verify range values
        $this->assertCount(10, $seq);
        $this->assertSame(10, $seq[0]);
        $this->assertSame(1, $seq[9]);
    }

    /**
     * Test range method with floats.
     */
    public function testRangeWithFloats(): void
    {
        // Test: Create range with float step
        $seq = Sequence::range(0.0, 1.0, 0.2);

        // Test: Verify range contains float values
        $this->assertGreaterThan(4, $seq->count());
        $this->assertIsFloat($seq[0]);
    }

    /**
     * Test range method throws ValueError for zero step.
     */
    public function testRangeThrowsValueErrorForZeroStep(): void
    {
        // Test: Attempt to create range with zero step
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("The step size cannot be zero");

        Sequence::range(1, 10, 0);
    }

    /**
     * Test range method throws ValueError for invalid positive step.
     */
    public function testRangeThrowsValueErrorForInvalidPositiveStep(): void
    {
        // Test: Attempt descending range with positive step
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("The step size must be negative for a decreasing range");

        Sequence::range(10, 1, 1);
    }

    /**
     * Test range method throws ValueError for invalid negative step.
     */
    public function testRangeThrowsValueErrorForInvalidNegativeStep(): void
    {
        // Test: Attempt ascending range with negative step
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("The step size must be positive for an increasing range");

        Sequence::range(1, 10, -1);
    }
}
