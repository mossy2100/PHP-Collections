<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Set;

use Galaxon\Collections\Set;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Tests for Set constructor and factory methods.
 */
#[CoversClass(Set::class)]
class SetConstructorTest extends TestCase
{
    /**
     * Test basic constructor with no type constraints.
     */
    public function testConstructorWithoutTypeConstraints(): void
    {
        $set = new Set();

        $this->assertInstanceOf(Set::class, $set);
        $this->assertCount(0, $set);
        $this->assertTrue($set->empty());
    }

    /**
     * Test constructor with string type constraint.
     */
    public function testConstructorWithStringType(): void
    {
        $set = new Set('string');

        $this->assertInstanceOf(Set::class, $set);
        $this->assertCount(0, $set);
    }

    /**
     * Test constructor with int type constraint.
     */
    public function testConstructorWithIntType(): void
    {
        $set = new Set('int');

        $this->assertInstanceOf(Set::class, $set);
        $this->assertCount(0, $set);
    }

    /**
     * Test constructor with union type constraint.
     */
    public function testConstructorWithUnionType(): void
    {
        $set = new Set('string|int');

        $this->assertTrue($set->valueTypes->containsOnly('string', 'int'));
    }

    /**
     * Test constructor with nullable type constraint.
     */
    public function testConstructorWithNullableType(): void
    {
        $set = new Set('?int');

        $this->assertTrue($set->valueTypes->containsOnly('null', 'int'));
    }

    /**
     * Test constructor with source array.
     */
    public function testConstructorWithSourceArray(): void
    {
        $source = [1, 2, 3, 4, 5];
        $set = new Set(source: $source);

        $this->assertCount(5, $set);
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains(5));
    }

    /**
     * Test constructor removes duplicates.
     */
    public function testConstructorRemovesDuplicates(): void
    {
        $source = [1, 2, 2, 3, 3, 3, 4];
        $set = new Set(source: $source);

        $this->assertCount(4, $set);
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains(2));
        $this->assertTrue($set->contains(3));
        $this->assertTrue($set->contains(4));
    }

    /**
     * Test constructor with mixed types.
     */
    public function testConstructorWithMixedTypes(): void
    {
        $source = [1, 'two', 3.0, true];
        $set = new Set(source: $source);

        $this->assertCount(4, $set);
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains('two'));
        $this->assertTrue($set->contains(3.0));
        $this->assertTrue($set->contains(true));
    }

    /**
     * Test constructor with empty source array.
     */
    public function testConstructorWithEmptySourceArray(): void
    {
        $set = new Set(source: []);

        $this->assertCount(0, $set);
        $this->assertTrue($set->empty());
    }

    /**
     * Test constructor infers types.
     */
    public function testConstructorInfersTypes(): void
    {
        $set = new Set(source: [1, 2, 3]);

        $this->assertTrue($set->valueTypes->contains('int'));
        $this->assertCount(1, $set->valueTypes);
    }

    /**
     * Test constructor infers nullable types when null values present.
     */
    public function testConstructorInfersNullableTypes(): void
    {
        $set = new Set(source: [1, null, 3, null, 5]);

        $this->assertCount(4, $set); // Duplicate nulls removed
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains(null));
        $this->assertTrue($set->contains(5));

        $this->assertTrue($set->valueTypes->containsAll('int', 'null'));
        $this->assertCount(2, $set->valueTypes);
    }

    /**
     * Test constructor with explicit types (not inferred).
     */
    public function testConstructorWithExplicitTypesAndSource(): void
    {
        $set = new Set('int', [1, 2, 3]);

        $this->assertTrue($set->valueTypes->containsOnly('int'));
    }

    /**
     * Test constructor with union type string.
     */
    public function testConstructorWithUnionTypeString(): void
    {
        $set = new Set('int|string', [1, 'hello', 2, 'world']);

        $this->assertCount(4, $set);
        $this->assertTrue($set->valueTypes->containsOnly('int', 'string'));
    }

    /**
     * Test constructor throws TypeError when explicit type doesn't match values.
     */
    public function testConstructorThrowsTypeErrorForMismatchedExplicitType(): void
    {
        $this->expectException(TypeError::class);

        new Set('string', [1, 2, 3]);
    }

    /**
     * Test constructor with generator and type inference.
     */
    public function testConstructorWithGeneratorAndTypeInference(): void
    {
        $generator = function () {
            yield 10;
            yield 20;
            yield 30;
        };

        $set = new Set(source: $generator());

        $this->assertCount(3, $set);
        $this->assertTrue($set->contains(10));
        $this->assertTrue($set->contains(30));
        $this->assertTrue($set->valueTypes->contains('int'));
    }

    /**
     * Test constructor with null type parameter (any type allowed).
     */
    public function testConstructorWithNullTypeParameter(): void
    {
        $set = new Set(null, [1, 'hello', 3.14]);

        $this->assertCount(3, $set);
        $this->assertTrue($set->valueTypes->anyOk());
    }

    /**
     * Test constructor infers multiple types correctly.
     */
    public function testConstructorInfersMultipleTypes(): void
    {
        $set = new Set(source: [1, 'hello', 3.14, true, false, null, []]);

        $this->assertTrue($set->valueTypes->containsOnly('int', 'string', 'float', 'bool', 'null', 'array'));
    }

    /**
     * Test constructor with only null values.
     */
    public function testConstructorWithOnlyNullValues(): void
    {
        $set = new Set(source: [null, null, null]);

        $this->assertCount(1, $set); // Only one null in set
        $this->assertTrue($set->valueTypes->contains('null'));
        $this->assertTrue($set->contains(null));
    }

    /**
     * Test constructor with another Set as source.
     */
    public function testConstructorWithSetAsSource(): void
    {
        $original = new Set('int');
        $original->add(1, 2, 3);

        $copy = new Set(source: $original);

        $this->assertCount(3, $copy);
        $this->assertTrue($copy->contains(1));
        $this->assertTrue($copy->contains(3));

        // Test they are separate instances.
        $original->add(4);
        $this->assertCount(4, $original);
        $this->assertCount(3, $copy);
    }
}
