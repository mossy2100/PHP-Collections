<?php

declare(strict_types=1);

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
     * Test constructor adds null to typeset when default cannot be inferred.
     */
    public function testConstructorAddsNullWhenNoOtherDefaultCanBeInferred(): void
    {
        // Test: Create a Sequence with DateTime type and no default.
        $seq = new Sequence('DateTime');

        // Check there are two types in the typeset, null and DateTime.
        $this->assertTrue($seq->valueTypes->containsOnly('null', 'DateTime'));
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
     * Test constructor with source array.
     */
    public function testConstructorWithSourceArray(): void
    {
        // Test: Create Sequence from array
        $source = [1, 2, 3, 4, 5];
        $seq = new Sequence(source: $source);

        // Test: Verify all items are copied
        $this->assertCount(5, $seq);
        $this->assertSame(1, $seq[0]);
        $this->assertSame(5, $seq[4]);
    }

    /**
     * Test constructor with mixed types.
     */
    public function testConstructorWithMixedTypes(): void
    {
        // Test: Create Sequence from mixed type array
        $source = [1, 'two', 3.0, true];
        $seq = new Sequence(source: $source);

        // Test: Verify all items and types are preserved
        $this->assertCount(4, $seq);
        $this->assertSame(1, $seq[0]);
        $this->assertSame('two', $seq[1]);
        $this->assertSame(3.0, $seq[2]);
        $this->assertTrue($seq[3]);
    }

    /**
     * Test constructor with empty source array.
     */
    public function testConstructorWithEmptySourceArray(): void
    {
        // Test: Create Sequence from empty array
        $seq = new Sequence(source: []);

        // Test: Verify Sequence is empty
        $this->assertCount(0, $seq);
    }

    /**
     * Test constructor infers nullable types when null values present.
     */
    public function testConstructorInfersNullableTypes(): void
    {
        // Test: Create Sequence with null values (types inferred)
        $seq = new Sequence(source: [1, null, 3, null, 5]);

        // Test: Verify all items preserved
        $this->assertCount(5, $seq);
        $this->assertSame(1, $seq[0]);
        $this->assertNull($seq[1]);
        $this->assertSame(5, $seq[4]);

        // Test: Verify both int and null types were inferred
        $this->assertTrue($seq->valueTypes->containsAll('int', 'null'));
        $this->assertCount(2, $seq->valueTypes);
    }

    /**
     * Test constructor with explicit types (not inferred).
     */
    public function testConstructorWithExplicitTypesAndSource(): void
    {
        // Test: Create Sequence with explicit type constraint
        $seq = new Sequence('int', null, [1, 2, 3]);

        // Test: Verify type constraint applied
        $this->assertTrue($seq->valueTypes->containsOnly('int'));
    }

    /**
     * Test constructor with explicit types and custom default.
     */
    public function testConstructorWithExplicitTypesAndCustomDefault(): void
    {
        // Test: Create Sequence with explicit types and custom default
        $seq = new Sequence('int', 99, [1, 2, 3]);

        // Test: Verify custom default value set
        $this->assertSame(99, $seq->defaultValue);
        $this->assertCount(3, $seq);
    }

    /**
     * Test constructor with type inference and custom default.
     */
    public function testConstructorWithTypeInferenceAndCustomDefault(): void
    {
        // Test: Create Sequence with type inference and custom default
        $seq = new Sequence(true, 0, [1, 2, 3]);

        // Test: Verify types inferred and default set
        $this->assertTrue($seq->valueTypes->contains('int'));
        $this->assertSame(0, $seq->defaultValue);
    }

    /**
     * Test constructor infers multiple types correctly.
     */
    public function testConstructorInfersMultipleTypes(): void
    {
        // Test: Create Sequence with various types
        $seq = new Sequence(source: [1, 'hello', 3.14, true, false, null, []]);

        // Test: Verify all unique types were inferred
        $this->assertTrue($seq->valueTypes->containsOnly('int', 'string', 'float', 'bool', 'null', 'array'));
    }

    /**
     * Test constructor with only null values.
     */
    public function testConstructorWithOnlyNullValues(): void
    {
        // Test: Create Sequence containing only nulls
        $seq = new Sequence(source: [null, null, null]);

        // Test: Verify null type inferred
        $this->assertCount(3, $seq);
        $this->assertTrue($seq->valueTypes->contains('null'));
        $this->assertNull($seq[0]);
        $this->assertNull($seq[2]);
    }

    /**
     * Test constructor throws TypeError when explicit type doesn't match values.
     */
    public function testConstructorThrowsTypeErrorForMismatchedExplicitType(): void
    {
        // Test: Attempt to create Sequence with mismatched type
        $this->expectException(TypeError::class);

        new Sequence('string', null, [1, 2, 3]);
    }

    /**
     * Test constructor with generator and type inference.
     */
    public function testConstructorWithGeneratorAndTypeInference(): void
    {
        // Test: Create Sequence from generator with type inference
        $generator = function () {
            yield 10;
            yield 20;
            yield 30;
        };

        $seq = new Sequence(source: $generator());

        // Test: Verify items and types
        $this->assertCount(3, $seq);
        $this->assertSame(10, $seq[0]);
        $this->assertSame(30, $seq[2]);
        $this->assertTrue($seq->valueTypes->contains('int'));
    }

    /**
     * Test constructor infers default value when types are inferred.
     */
    public function testConstructorInfersDefaultValueWhenTypesInferred(): void
    {
        // Test: Create Sequence with inferred int type
        $seq = new Sequence(source: [1, 2, 3]);

        // Test: Verify default value inferred as 0 for int
        $this->assertSame(0, $seq->defaultValue);
    }

    /**
     * Test constructor infers default value with mixed types.
     */
    public function testConstructorInfersDefaultValueWithMixedTypes(): void
    {
        // Test: Create Sequence with mixed types including null
        $seq = new Sequence(source: [1, 'hello', null]);

        // Test: Verify default value is null (since null is an option)
        $this->assertNull($seq->defaultValue);
    }

    /**
     * Test constructor with null type parameter explicitly.
     */
    public function testConstructorWithNullTypeParameter(): void
    {
        // Test: Create Sequence with null as types parameter (any type allowed)
        $seq = new Sequence(null, null, [1, 'hello', 3.14]);

        // Test: Verify no type constraints applied
        $this->assertCount(3, $seq);
        // When types is null, no specific types are added to the TypeSet
        $this->assertTrue($seq->valueTypes->anyOk());
    }

    /**
     * Test constructor with union type string.
     */
    public function testConstructorWithUnionTypeString(): void
    {
        // Test: Create Sequence with union type constraint
        $seq = new Sequence('int|string', null, [1, 'hello', 2, 'world']);

        // Test: Verify both types accepted
        $this->assertCount(4, $seq);
        $this->assertTrue($seq->valueTypes->containsOnly('int', 'string'));
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
