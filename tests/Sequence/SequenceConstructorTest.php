<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Sequence;

use DomainException;
use InvalidArgumentException;
use LengthException;
use Mossy2100\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

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
        $this->assertTrue($seq->valueTypes->containsOnly('string'));
    }

    /**
     * Test constructor with int type constraint.
     */
    public function testConstructorWithIntType(): void
    {
        // Test: Create a Sequence with int type constraint
        $seq = new Sequence('int');

        $this->assertTrue($seq->valueTypes->containsOnly('int'));
    }

    /**
     * Test constructor with float type constraint.
     */
    public function testConstructorWithFloatType(): void
    {
        // Test: Create a Sequence with float type constraint
        $seq = new Sequence('float');

        $this->assertTrue($seq->valueTypes->containsOnly('float'));
    }

    /**
     * Test constructor with bool type constraint.
     */
    public function testConstructorWithBoolType(): void
    {
        // Test: Create a Sequence with bool type constraint
        $seq = new Sequence('bool');

        $this->assertTrue($seq->valueTypes->containsOnly('bool'));
    }

    /**
     * Test constructor with array type constraint.
     */
    public function testConstructorWithArrayType(): void
    {
        // Test: Create a Sequence with array type constraint
        $seq = new Sequence('array');

        $this->assertTrue($seq->valueTypes->containsOnly('array'));
    }

    /**
     * Test constructor with nullable type constraint.
     */
    public function testConstructorWithNullableType(): void
    {
        // Test: Create a Sequence with nullable int type
        $seq = new Sequence('?int');

        $this->assertTrue($seq->valueTypes->containsAll('int', 'null'));
    }

    /**
     * Test constructor with union type constraint.
     */
    public function testConstructorWithUnionType(): void
    {
        // Test: Create a Sequence with union type constraint
        $seq = new Sequence('string|int');

        $this->assertTrue($seq->valueTypes->containsAll('string', 'int'));
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
        $seq = new Sequence('int', [1, 2, 3]);

        // Test: Verify type constraint applied
        $this->assertTrue($seq->valueTypes->containsOnly('int'));
    }

    /**
     * Test constructor with explicit types and custom default.
     */
    public function testConstructorWithExplicitTypes(): void
    {
        // Test: Create Sequence with explicit types and custom default
        $seq = new Sequence('int', [1, 2, 3]);

        $this->assertCount(3, $seq);
    }

    /**
     * Test constructor with type inference.
     */
    public function testConstructorWithTypeInferenceAndCustomDefault(): void
    {
        // Test: Create Sequence with type inference
        $seq = new Sequence(true, [1, 2, 3]);

        // Test: Verify types inferred
        $this->assertTrue($seq->valueTypes->contains('int'));
    }

    /**
     * Test constructor infers multiple types correctly.
     */
    public function testConstructorInfersMultipleTypes(): void
    {
        // Test: Create Sequence with various types
        $seq = new Sequence(source: [
            1,
            'hello',
            3.14,
            true,
            false,
            null,
            [],
        ]);

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
     * Test constructor throws InvalidArgumentException when explicit type doesn't match values.
     */
    public function testConstructorThrowsInvalidArgumentExceptionForMismatchedExplicitType(): void
    {
        // Test: Attempt to create Sequence with mismatched type
        $this->expectException(InvalidArgumentException::class);

        new Sequence('string', [1, 2, 3]);
    }

    /**
     * Test constructor with generator and type inference.
     */
    public function testConstructorWithGeneratorAndTypeInference(): void
    {
        // Test: Create Sequence from generator with type inference
        $generator = static function () {
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
     * Test constructor with null type parameter explicitly.
     */
    public function testConstructorWithNullTypeParameter(): void
    {
        // Test: Create Sequence with null as types parameter (any type allowed)
        $seq = new Sequence(null, [1, 'hello', 3.14]);

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
        $seq = new Sequence('int|string', [1, 'hello', 2, 'world']);

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
     * Test range method throws DomainException for zero step.
     */
    public function testRangeThrowsDomainExceptionForZeroStep(): void
    {
        // Test: Attempt to create range with zero step
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot create range with a zero step size');

        Sequence::range(1, 10, 0);
    }

    /**
     * Test range method throws DomainException for invalid positive step.
     */
    public function testRangeThrowsDomainExceptionForInvalidPositiveStep(): void
    {
        // Test: Attempt descending range with positive step
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot create a decreasing range with a positive step size');

        Sequence::range(10, 1, 1);
    }

    /**
     * Test range method throws DomainException for invalid negative step.
     */
    public function testRangeThrowsDomainExceptionForInvalidNegativeStep(): void
    {
        // Test: Attempt ascending range with negative step
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot create an increasing range with a negative step size');

        Sequence::range(1, 10, -1);
    }

    /**
     * Test range with start equal to end returns just the start value, for either step sign.
     */
    public function testRangeStartEqualsEnd(): void
    {
        $this->assertSame([5], Sequence::range(5, 5)->toArray());
        $this->assertSame([5], Sequence::range(5, 5, -1)->toArray());
        $this->assertSame([5.0], Sequence::range(5.0, 5.0)->toArray());
    }

    /**
     * Test range with a step larger than the span returns just the start value.
     */
    public function testRangeStepLargerThanSpan(): void
    {
        $this->assertSame([0], Sequence::range(0, 5, 10)->toArray());
        $this->assertSame([5], Sequence::range(5, 0, -10)->toArray());
        $this->assertSame([0.0], Sequence::range(0, 1, 2.5)->toArray());
    }

    /**
     * Test range with a positive step for a descending range is rejected, but a negative step works.
     */
    public function testRangeDescendingWithStep(): void
    {
        $this->assertSame([10, 7, 4, 1], Sequence::range(10, 1, -3)->toArray());
    }

    /**
     * Test range produces floats when any argument is a float, even if the values are integral.
     */
    public function testRangeFloatType(): void
    {
        $seq = Sequence::range(1, 3, 1.0);

        $this->assertSame([1.0, 2.0, 3.0], $seq->toArray());
        $this->assertTrue($seq->valueTypes->containsOnly('float'));
        $this->assertTrue(Sequence::range(1, 3)->valueTypes->containsOnly('int'));
    }

    /**
     * Test range with a float step ends exactly on the end value.
     */
    public function testRangeFloatStepEndsOnEnd(): void
    {
        $seq = Sequence::range(0, 1, 0.1);

        $this->assertCount(11, $seq);
        $this->assertSame(1.0, $seq->last());
        $this->assertSame(0.30000000000000004, $seq[3]);
    }

    /**
     * Test range matches PHP's range() for float steps, which compute each value as start + i * step.
     */
    public function testRangeFloatSweepMatchesNativeRange(): void
    {
        $steps = [
            0.1,
            0.2,
            0.3,
            0.7,
            0.01,
            1 / 3,
        ];
        foreach ($steps as $step) {
            for ($n = 1; $n <= 50; $n++) {
                $end = $n * $step;
                $expected = range(0.0, $end, $step);
                $this->assertSame($expected, Sequence::range(0, $end, $step)->toArray(), "end=$end, step=$step");

                $expected = range($end, 0.0, $step);
                $this->assertSame($expected, Sequence::range($end, 0, -$step)->toArray(), "end=$end, step=-$step");
            }
        }
    }

    /**
     * Test range at the limits of the int range doesn't overflow to float.
     */
    public function testRangeAtIntLimits(): void
    {
        $this->assertSame(
            [
                PHP_INT_MAX - 2,
                PHP_INT_MAX - 1,
                PHP_INT_MAX,
            ],
            Sequence::range(PHP_INT_MAX - 2, PHP_INT_MAX)->toArray()
        );
        $this->assertSame(
            [
                PHP_INT_MIN + 2,
                PHP_INT_MIN + 1,
                PHP_INT_MIN,
            ],
            Sequence::range(PHP_INT_MIN + 2, PHP_INT_MIN, -1)->toArray()
        );
    }

    /**
     * Data provider for non-finite range arguments.
     *
     * @return array<string, array{float|int, float|int, float|int, string}>
     */
    public static function nonFiniteRangeProvider(): array
    {
        return [
            'start NAN' => [NAN, 1, 1, 'Invalid start: NAN. Must be finite.'],
            'end INF'   => [0, INF, 1, 'Invalid end: INF. Must be finite.'],
            'end -INF'  => [0, -INF, -1, 'Invalid end: -INF. Must be finite.'],
            'step NAN'  => [0, 1, NAN, 'Invalid step size: NAN. Must be finite.'],
            'step INF'  => [0, 1, INF, 'Invalid step size: INF. Must be finite.'],
        ];
    }

    /**
     * Test range rejects non-finite arguments.
     */
    #[DataProvider('nonFiniteRangeProvider')]
    public function testRangeThrowsDomainExceptionForNonFiniteArgument(
        float|int $start,
        float|int $end,
        float|int $step,
        string $message
    ): void {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage($message);

        Sequence::range($start, $end, $step);
    }

    /**
     * Test range throws LengthException when there would be too many values.
     */
    public function testRangeThrowsLengthExceptionForTooManyValues(): void
    {
        $this->expectException(LengthException::class);

        Sequence::range(0, 1e20);
    }

    /**
     * Test type inference from anonymous-class objects.
     */
    public function testConstructorInfersTypeFromAnonymousClass(): void
    {
        $seq = new Sequence(source: [
            new class {
            },
        ]);

        $this->assertTrue($seq->valueTypes->containsOnly('object'));
    }
}
