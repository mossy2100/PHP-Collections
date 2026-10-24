<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Sequence;

use LengthException;
use Mossy2100\Collections\Dictionary;
use Mossy2100\Collections\Sequence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

/**
 * Tests for Sequence aggregation methods.
 */
#[CoversClass(Sequence::class)]
class SequenceAggregationTest extends TestCase
{
    #region Method countValues() tests.

    /**
     * Test countValues method.
     */
    public function testCountValues(): void
    {
        // Test: Count occurrences of each value
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'a', 'c', 'b', 'a');
        $counts = $seq->countValues();

        // Test: Verify counts are correct
        $this->assertInstanceOf(Dictionary::class, $counts);
        $this->assertSame(3, $counts['a']);
        $this->assertSame(2, $counts['b']);
        $this->assertSame(1, $counts['c']);
    }

    /**
     * Test countValues with unique values.
     */
    public function testCountValuesWithUniqueValues(): void
    {
        // Test: Count when all values are unique
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $counts = $seq->countValues();

        // Test: Verify all counts are 1
        $this->assertCount(5, $counts);
        $this->assertSame(1, $counts[1]);
        $this->assertSame(1, $counts[5]);
    }

    #endregion

    #region Method reduce() tests.

    /**
     * Test reduce method aggregates values.
     */
    public function testReduceAggregatesValues(): void
    {
        // Test: Sum using reduce
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $sum = $seq->reduce(static fn ($acc, $x) => $acc + $x, 0);

        // Test: Verify correct sum
        $this->assertSame(15, $sum);
    }

    /**
     * Test reduce with different operation.
     */
    public function testReduceWithConcatenation(): void
    {
        // Test: Concatenate strings
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'c');
        $result = $seq->reduce(static fn ($acc, $x) => $acc . $x, '');

        // Test: Verify concatenation
        $this->assertSame('abc', $result);
    }

    #endregion

    #region Method product() tests.

    /**
     * Test product method with integers.
     */
    public function testProduct(): void
    {
        // Test: Calculate product
        $seq = new Sequence('int');
        $seq->append(2, 3, 4);

        // Test: Verify product calculation
        $this->assertSame(24, $seq->product());
    }

    /**
     * Test product method with floats.
     */
    public function testProductWithFloats(): void
    {
        // Test: Calculate product with floats
        $seq = new Sequence('float');
        $seq->append(2.5, 4.0, 2.0);

        // Test: Verify product calculation
        $this->assertSame(20.0, $seq->product());
    }

    /**
     * Test product method with mixed int and float.
     */
    public function testProductWithMixedNumericTypes(): void
    {
        // Test: Calculate product with mixed types
        $seq = new Sequence('int|float');
        $seq->append(2, 3.5, 2);

        // Test: Verify product calculation (result is float)
        $this->assertSame(14.0, $seq->product());
    }

    /**
     * Test product method with empty sequence returns multiplicative identity.
     */
    public function testProductWithEmptySequence(): void
    {
        // Test: Calculate product of empty sequence
        $seq = new Sequence('int');

        // Test: Verify returns 1 (multiplicative identity)
        $this->assertSame(1, $seq->product());
    }

    /**
     * Test product method with single value.
     */
    public function testProductWithSingleValue(): void
    {
        // Test: Calculate product with one value
        $seq = new Sequence('int');
        $seq->append(42);

        // Test: Verify returns the value itself
        $this->assertSame(42, $seq->product());
    }

    /**
     * Test product method throws TypeError for non-numeric values.
     */
    public function testProductThrowsTypeErrorForNonNumericValues(): void
    {
        // Test: Attempt to calculate product with strings
        $seq = new Sequence('string');
        $seq->append('1', '2', '3');

        $this->expectException(TypeError::class);
        $seq->product();
    }

    /**
     * Test product method with negative numbers.
     */
    public function testProductWithNegativeNumbers(): void
    {
        // Test: Calculate product with negative numbers
        $seq = new Sequence('int');
        $seq->append(-2, 3, -4);

        // Test: Verify product calculation
        $this->assertSame(24, $seq->product());
    }

    #endregion

    #region Method sum() tests.

    /**
     * Test sum method with integers.
     */
    public function testSum(): void
    {
        // Test: Calculate sum
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);

        // Test: Verify sum calculation
        $this->assertSame(15, $seq->sum());
    }

    /**
     * Test sum method with floats.
     */
    public function testSumWithFloats(): void
    {
        // Test: Calculate sum with floats
        $seq = new Sequence('float');
        $seq->append(1.5, 2.5, 3.5);

        // Test: Verify sum calculation
        $this->assertSame(7.5, $seq->sum());
    }

    /**
     * Test sum method with mixed int and float.
     */
    public function testSumWithMixedNumericTypes(): void
    {
        // Test: Calculate sum with mixed types
        $seq = new Sequence('int|float');
        $seq->append(1, 2.5, 3);

        // Test: Verify sum calculation (result is float)
        $this->assertSame(6.5, $seq->sum());
    }

    /**
     * Test sum method with empty sequence returns additive identity.
     */
    public function testSumWithEmptySequence(): void
    {
        // Test: Calculate sum of empty sequence
        $seq = new Sequence('int');

        // Test: Verify returns 0 (additive identity)
        $this->assertSame(0, $seq->sum());
    }

    /**
     * Test sum method with single value.
     */
    public function testSumWithSingleValue(): void
    {
        // Test: Calculate sum with one value
        $seq = new Sequence('int');
        $seq->append(42);

        // Test: Verify returns the value itself
        $this->assertSame(42, $seq->sum());
    }

    /**
     * Test sum method throws TypeError for non-numeric values.
     */
    public function testSumThrowsTypeErrorForNonNumericValues(): void
    {
        // Test: Attempt to calculate sum with strings
        $seq = new Sequence('string');
        $seq->append('1', '2', '3');

        $this->expectException(TypeError::class);
        $seq->sum();
    }

    /**
     * Test sum method with negative numbers.
     */
    public function testSumWithNegativeNumbers(): void
    {
        // Test: Calculate sum with negative numbers
        $seq = new Sequence('int');
        $seq->append(-5, 3, -2, 8);

        // Test: Verify sum calculation
        $this->assertSame(4, $seq->sum());
    }

    /**
     * Test sum method with zero.
     */
    public function testSumWithZero(): void
    {
        // Test: Calculate sum with zeros
        $seq = new Sequence('int');
        $seq->append(0, 5, 0, 3);

        // Test: Verify sum calculation
        $this->assertSame(8, $seq->sum());
    }

    #endregion

    #region Method min() tests.

    /**
     * Test min method.
     */
    public function testMin(): void
    {
        // Test: Find minimum value
        $seq = new Sequence('int');
        $seq->append(5, 2, 8, 1, 9, 3);

        // Test: Verify minimum value
        $this->assertSame(1, $seq->min());
    }

    /**
     * Test min method with floats.
     */
    public function testMinWithFloats(): void
    {
        // Test: Find minimum float value
        $seq = new Sequence('float');
        $seq->append(3.14, 2.71, 1.41, 4.67);

        // Test: Verify minimum float value
        $this->assertSame(1.41, $seq->min());
    }

    /**
     * Test min method with negative numbers.
     */
    public function testMinWithNegativeNumbers(): void
    {
        // Test: Find minimum with negative values
        $seq = new Sequence('int');
        $seq->append(5, -2, 8, -10, 3);

        // Test: Verify minimum negative value
        $this->assertSame(-10, $seq->min());
    }

    /**
     * Test min method throws LengthException on empty Sequence.
     */
    public function testMinThrowsLengthExceptionOnEmpty(): void
    {
        // Test: Attempt to find min of empty Sequence
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('Cannot find the minimum value of an empty Sequence.');

        $seq = new Sequence('int');
        $seq->min();
    }

    #endregion

    #region Method max() tests.

    /**
     * Test max method.
     */
    public function testMax(): void
    {
        // Test: Find maximum value
        $seq = new Sequence('int');
        $seq->append(5, 2, 8, 1, 9, 3);

        // Test: Verify maximum value
        $this->assertSame(9, $seq->max());
    }

    /**
     * Test max method with floats.
     */
    public function testMaxWithFloats(): void
    {
        // Test: Find maximum float value
        $seq = new Sequence('float');
        $seq->append(3.14, 2.71, 1.41, 4.67);

        // Test: Verify maximum float value
        $this->assertSame(4.67, $seq->max());
    }

    /**
     * Test max method with negative numbers.
     */
    public function testMaxWithNegativeNumbers(): void
    {
        // Test: Find maximum with negative values
        $seq = new Sequence('int');
        $seq->append(-5, -2, -8, -1, -3);

        // Test: Verify maximum negative value
        $this->assertSame(-1, $seq->max());
    }

    /**
     * Test max method throws LengthException on empty Sequence.
     */
    public function testMaxThrowsLengthExceptionOnEmpty(): void
    {
        // Test: Attempt to find max of empty Sequence
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('Cannot find the maximum value of an empty Sequence.');

        $seq = new Sequence('int');
        $seq->max();
    }

    #endregion

    #region Method average() tests.

    /**
     * Test average method.
     */
    public function testAverage(): void
    {
        // Test: Calculate average
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);

        // Test: Verify average calculation
        $this->assertSame(3, $seq->average());
    }

    /**
     * Test average method with floats.
     */
    public function testAverageWithFloats(): void
    {
        // Test: Calculate average of floats
        $seq = new Sequence('float');
        $seq->append(1.5, 2.5, 3.5, 4.5);

        // Test: Verify average calculation
        $this->assertSame(3.0, $seq->average());
    }

    /**
     * Test average method returns float for division.
     */
    public function testAverageReturnsFloat(): void
    {
        // Test: Calculate average that results in float
        $seq = new Sequence('int');
        $seq->append(1, 2, 3);

        // Test: Verify average is 2.0 (float)
        $this->assertEquals(2.0, $seq->average());
    }

    /**
     * Test average method with single item.
     */
    public function testAverageWithSingleItem(): void
    {
        // Test: Calculate average of single item
        $seq = new Sequence('int');
        $seq->append(42);

        // Test: Verify average equals the single item
        $this->assertSame(42, $seq->average());
    }

    /**
     * Test average method throws LengthException on empty Sequence.
     */
    public function testAverageThrowsLengthExceptionOnEmpty(): void
    {
        // Test: Attempt to find average of empty Sequence
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('Cannot calculate the average of an empty Sequence.');

        $seq = new Sequence('int');
        $seq->average();
    }

    #endregion

    #region Method join() tests.

    /**
     * Test join method without glue.
     */
    public function testJoinWithoutGlue(): void
    {
        // Test: Join strings without separator
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'c');

        // Test: Verify joined string
        $this->assertSame('abc', $seq->join());
    }

    /**
     * Test join method with glue.
     */
    public function testJoinWithGlue(): void
    {
        // Test: Join with separator
        $seq = new Sequence('string');
        $seq->append('apple', 'banana', 'cherry');

        // Test: Verify joined with separator
        $this->assertSame('apple, banana, cherry', $seq->join(', '));
    }

    /**
     * Test join method with objects that implement __toString().
     */
    public function testJoinWithStringableObjects(): void
    {
        // Test: Join with objects that can be converted to strings
        $obj1 = new class {
            public function __toString(): string
            {
                return 'first';
            }
        };
        $obj2 = new class {
            public function __toString(): string
            {
                return 'second';
            }
        };

        $seq = new Sequence('object');
        $seq->append($obj1, $obj2);

        // Test: Verify objects are converted to strings
        $this->assertSame('first, second', $seq->join(', '));
    }

    /**
     * Test join method with non-Stringable objects converts via Stringify.
     */
    public function testJoinWithNonStringableObjects(): void
    {
        $seq = new Sequence('object');
        $seq->append(new stdClass(), new stdClass());

        // Stringify includes each object's ID.
        $result = $seq->join(', ');
        $this->assertMatchesRegularExpression('/^stdClass #\\d+ \\{\\}, stdClass #\\d+ \\{\\}$/', $result);
    }

    /**
     * Test join converts non-string scalars, null and arrays via Stringify.
     */
    public function testJoinWithMixedTypes(): void
    {
        $seq = new Sequence(source: [
            1,
            2.0,
            true,
            null,
            'x',
            [1, 'a'],
        ]);

        $this->assertSame("1, 2.0, true, null, x, [1, 'a']", $seq->join(', '));
    }

    #endregion
}
