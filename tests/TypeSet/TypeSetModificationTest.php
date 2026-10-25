<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\TypeSet;

use DateTime;
use DomainException;
use InvalidArgumentException;
use Mossy2100\Collections\Tests\Fixtures\ChildClass;
use Mossy2100\Collections\Tests\Fixtures\ParentClass;
use Mossy2100\Collections\TypeSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for TypeSet modification methods.
 */
#[CoversClass(TypeSet::class)]
class TypeSetModificationTest extends TestCase
{
    #region Method add() tests.

    /**
     * Test add() with single type.
     */
    public function testAddSingleType(): void
    {
        $ts = new TypeSet();
        $result = $ts->add('int');

        $this->assertSame($ts, $result); // Fluent interface
        $this->assertTrue($ts->contains('int'));
    }

    /**
     * Test add() with union type string.
     */
    public function testAddUnionTypeString(): void
    {
        $ts = new TypeSet();
        $ts->add('int|string|bool');

        $this->assertCount(3, $ts);
        $this->assertTrue($ts->containsAll('int', 'string', 'bool'));
    }

    /**
     * Test add() with nullable type.
     */
    public function testAddNullableType(): void
    {
        $ts = new TypeSet();
        $ts->add('?string');

        $this->assertCount(2, $ts);
        $this->assertTrue($ts->contains('string'));
        $this->assertTrue($ts->contains('null'));
    }

    /**
     * Test add() with array of types.
     */
    public function testAddArrayOfTypes(): void
    {
        $ts = new TypeSet();
        $ts->add(['int', 'float', 'string']);

        $this->assertCount(3, $ts);
        $this->assertTrue($ts->containsAll('int', 'float', 'string'));
    }

    /**
     * Test add() with duplicate types (should be ignored).
     */
    public function testAddDuplicateTypes(): void
    {
        $ts = new TypeSet('int');
        $ts->add('int');
        $ts->add(['int', 'string', 'int']);

        $this->assertCount(2, $ts); // Only int and string
        $this->assertTrue($ts->containsAll('int', 'string'));
    }

    /**
     * Test add() with whitespace in union type.
     */
    public function testAddWithWhitespace(): void
    {
        $ts = new TypeSet();
        $ts->add('int | string | bool');

        $this->assertCount(3, $ts);
        $this->assertTrue($ts->containsAll('int', 'string', 'bool'));
    }

    /**
     * Test add() with leading backslash on class name.
     */
    public function testAddWithLeadingBackslash(): void
    {
        $ts = new TypeSet();
        $ts->add('\DateTime');

        $this->assertTrue($ts->contains('DateTime'));
        $this->assertTrue($ts->contains('\DateTime')); // Both forms work
    }

    /**
     * Test add() with invalid type throws DomainException.
     */
    public function testAddInvalidTypeThrowsDomainException(): void
    {
        $ts = new TypeSet();

        $this->expectException(DomainException::class);
        $ts->add('123invalid');
    }

    /**
     * Test add() with non-string type throws InvalidArgumentException.
     */
    public function testAddNonStringThrowsInvalidArgumentException(): void
    {
        $ts = new TypeSet();

        $this->expectException(InvalidArgumentException::class);
        $ts->add([123]); // @phpstan-ignore argument.type (array with an integer instead of a string)
    }

    /**
     * Test add() lowercases PHP's reserved type names, which are case-insensitive.
     */
    public function testAddNormalizesReservedTypeCase(): void
    {
        $ts = new TypeSet('Int|?STRING|Mixed|\\Bool');

        $this->assertTrue($ts->containsOnly('int', 'null', 'string', 'mixed', 'bool'));
        $this->assertTrue(new TypeSet('Int')->match(1));
        $this->assertTrue(new TypeSet('int')->contains('INT'));
    }

    /**
     * Test add() keeps the case of non-reserved names, which could be class names.
     */
    public function testAddKeepsCaseOfNonReservedNames(): void
    {
        $ts = new TypeSet('Number|Scalar');

        $this->assertTrue($ts->containsOnly('Number', 'Scalar'));
        $this->assertFalse($ts->contains('number'));
        $this->assertFalse($ts->match(1));
    }

    /**
     * Data provider for unsupported type names.
     *
     * @return array<string, array{string}>
     */
    public static function unsupportedTypeProvider(): array
    {
        return [
            'void' => ['void'],
            'never' => ['never'],
            'false' => ['false'],
            'true' => ['true'],
            'self' => ['self'],
            'static' => ['static'],
            'parent' => ['parent'],
            'False' => ['False'],
            '?void' => ['?void'],
        ];
    }

    /**
     * Test add() rejects unsupported type names.
     */
    #[DataProvider('unsupportedTypeProvider')]
    public function testAddThrowsForUnsupportedType(string $type): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Unsupported type:');

        new TypeSet($type);
    }

    #endregion

    #region Method addValueType() tests.

    /**
     * Test addValueType() with various basic types.
     */
    public function testAddValueTypeWithBasicTypes(): void
    {
        $ts = new TypeSet();

        $ts->addValueType(null);
        $ts->addValueType(true);
        $ts->addValueType(42);
        $ts->addValueType(3.14);
        $ts->addValueType('hello');
        $ts->addValueType([]);

        $this->assertTrue($ts->containsAll('null', 'bool', 'int', 'float', 'string', 'array'));
    }

    /**
     * Test addValueType() with object.
     */
    public function testAddValueTypeWithObject(): void
    {
        $ts = new TypeSet();
        $ts->addValueType(new DateTime());

        $this->assertTrue($ts->contains('DateTime'));
    }

    /**
     * Test addValueType() with resource.
     */
    public function testAddValueTypeWithResource(): void
    {
        $ts = new TypeSet();
        $resource = fopen('php://memory', 'r');
        $this->assertIsResource($resource);

        $ts->addValueType($resource);
        fclose($resource);

        // Resource type will be something like 'resource (stream)'
        $this->assertCount(1, $ts);
    }

    /**
     * Test addValueType() with an anonymous class adds 'object'.
     */
    public function testAddValueTypeAnonymousClass(): void
    {
        $obj = new class {
        };
        $ts = new TypeSet()->addValueType($obj);

        $this->assertTrue($ts->containsOnly('object'));
        $this->assertTrue($ts->match($obj));
    }

    /**
     * Test addValueType() with an anonymous class that extends a class adds the parent class.
     */
    public function testAddValueTypeAnonymousSubclass(): void
    {
        $obj = new class extends ParentClass {
        };
        $ts = new TypeSet()->addValueType($obj);

        $this->assertTrue($ts->containsOnly(ParentClass::class));
        $this->assertTrue($ts->match($obj));
        $this->assertTrue($ts->match(new ChildClass()));
        $this->assertFalse($ts->match(new stdClass()));
    }

    #endregion

    #region Method addValueTypes() tests.

    /**
     * Test addValueTypes() adds the type of each value, once.
     */
    public function testAddValueTypes(): void
    {
        $ts = new TypeSet()->addValueTypes([1, 'a', 2, null, 3.5]);

        $this->assertTrue($ts->containsOnly('int', 'string', 'null', 'float'));
    }

    /**
     * Test addValueTypes() works with a generator and returns the same TypeSet.
     */
    public function testAddValueTypesWithGenerator(): void
    {
        $ts = new TypeSet('bool');
        $generator = (static function () {
            yield 1;
            yield new DateTime();
        })();

        $this->assertSame($ts, $ts->addValueTypes($generator));
        $this->assertTrue($ts->containsOnly('bool', 'int', 'DateTime'));
    }

    /**
     * Test addValueTypes() with no values leaves the TypeSet unchanged.
     */
    public function testAddValueTypesWithNoValues(): void
    {
        $this->assertTrue(new TypeSet()->addValueTypes([])->empty());
    }

    #endregion
}
