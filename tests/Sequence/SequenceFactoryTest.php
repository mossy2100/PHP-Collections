<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Sequence;

use DomainException;
use LengthException;
use Mossy2100\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Sequence factory methods.
 */
#[CoversClass(Sequence::class)]
class SequenceFactoryTest extends TestCase
{
    #region Method range() tests.

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
        $steps = [0.1, 0.2, 0.3, 0.7, 0.01, 1 / 3];
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
            [PHP_INT_MAX - 2, PHP_INT_MAX - 1, PHP_INT_MAX],
            Sequence::range(PHP_INT_MAX - 2, PHP_INT_MAX)->toArray()
        );
        $this->assertSame(
            [PHP_INT_MIN + 2, PHP_INT_MIN + 1, PHP_INT_MIN],
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
            'end INF' => [0, INF, 1, 'Invalid end: INF. Must be finite.'],
            'end -INF' => [0, -INF, -1, 'Invalid end: -INF. Must be finite.'],
            'step NAN' => [0, 1, NAN, 'Invalid step size: NAN. Must be finite.'],
            'step INF' => [0, 1, INF, 'Invalid step size: INF. Must be finite.'],
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

    #endregion
}
