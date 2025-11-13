<?php

declare(strict_types=1);

namespace Galaxon\Collections\Tests;

use ArrayObject;
use DateTime;
use Galaxon\Collections\TypeSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;
use ValueError;

// Test fixtures for inheritance and trait testing
class ParentClass
{
}

class ChildClass extends ParentClass
{
}

trait TestTrait
{
}

class ClassWithTrait
{
    use TestTrait;
}

/**
 * Tests for TypeSet class.
 */
#[CoversClass(TypeSet::class)]
class TypeSetTest extends TestCase
{
    // region Constructor tests

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
     * Test constructor with invalid type throws ValueError.
     */
    public function testConstructorWithInvalidTypeThrowsValueError(): void
    {
        $this->expectException(ValueError::class);
        new TypeSet('invalid-type-name');
    }

    // endregion

    // region add() method tests

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
     * Test add() with invalid type throws ValueError.
     */
    public function testAddInvalidTypeThrowsValueError(): void
    {
        $ts = new TypeSet();

        $this->expectException(ValueError::class);
        $ts->add('123invalid');
    }

    /**
     * Test add() with non-string type throws TypeError.
     */
    public function testAddNonStringThrowsTypeError(): void
    {
        $ts = new TypeSet();

        $this->expectException(TypeError::class);
        $ts->add([123]); // Array with integer instead of string
    }

    // endregion

    // region addValueType() method tests

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

    // endregion

    // region match() method tests

    /**
     * Test match() with basic types.
     */
    public function testMatchBasicTypes(): void
    {
        $ts = new TypeSet('int|string|bool');

        $this->assertTrue($ts->match(42));
        $this->assertTrue($ts->match('hello'));
        $this->assertTrue($ts->match(true));
        $this->assertFalse($ts->match(3.14));
        $this->assertFalse($ts->match([]));
    }

    /**
     * Test match() with null.
     */
    public function testMatchNull(): void
    {
        $ts = new TypeSet('?int');

        $this->assertTrue($ts->match(null));
        $this->assertTrue($ts->match(42));
        $this->assertFalse($ts->match('hello'));
    }

    /**
     * Test match() with scalar pseudotype.
     */
    public function testMatchScalar(): void
    {
        $ts = new TypeSet('scalar');

        $this->assertTrue($ts->match(42));
        $this->assertTrue($ts->match(3.14));
        $this->assertTrue($ts->match('hello'));
        $this->assertTrue($ts->match(true));
        $this->assertFalse($ts->match([]));
        $this->assertFalse($ts->match(null));
    }

    /**
     * Test match() with number pseudotype.
     */
    public function testMatchNumber(): void
    {
        $ts = new TypeSet('number');

        $this->assertTrue($ts->match(42));
        $this->assertTrue($ts->match(3.14));
        $this->assertFalse($ts->match('42'));
        $this->assertFalse($ts->match(true));
    }

    /**
     * Test match() with uint pseudotype.
     */
    public function testMatchUint(): void
    {
        $ts = new TypeSet('uint');

        $this->assertTrue($ts->match(0));
        $this->assertTrue($ts->match(42));
        $this->assertFalse($ts->match(-1));
        $this->assertFalse($ts->match(3.14));
    }

    /**
     * Test match() with iterable pseudotype.
     */
    public function testMatchIterable(): void
    {
        $ts = new TypeSet('iterable');

        $this->assertTrue($ts->match([]));
        $this->assertTrue($ts->match([1, 2, 3]));
        $this->assertFalse($ts->match('hello'));
        $this->assertFalse($ts->match(42));
    }

    /**
     * Test match() with callable pseudotype.
     */
    public function testMatchCallable(): void
    {
        $ts = new TypeSet('callable');

        $this->assertTrue($ts->match(fn() => null));
        $this->assertTrue($ts->match('strlen'));
        $this->assertFalse($ts->match('not a function'));
        $this->assertFalse($ts->match(42));
    }

    /**
     * Test match() with mixed pseudotype.
     */
    public function testMatchMixed(): void
    {
        $ts = new TypeSet('mixed');

        $this->assertTrue($ts->match(null));
        $this->assertTrue($ts->match(42));
        $this->assertTrue($ts->match('hello'));
        $this->assertTrue($ts->match([]));
        $this->assertTrue($ts->match(new DateTime()));
    }

    /**
     * Test match() with empty TypeSet (allows any type).
     */
    public function testMatchEmptyTypeSet(): void
    {
        $ts = new TypeSet();

        $this->assertTrue($ts->match(null));
        $this->assertTrue($ts->match(42));
        $this->assertTrue($ts->match('hello'));
        $this->assertTrue($ts->match([]));
    }

    /**
     * Test match() with object type.
     */
    public function testMatchObject(): void
    {
        $ts = new TypeSet('object');

        $this->assertTrue($ts->match(new DateTime()));
        $this->assertTrue($ts->match(new stdClass()));
        $this->assertFalse($ts->match(42));
        $this->assertFalse($ts->match('hello'));
    }

    /**
     * Test match() with class name.
     */
    public function testMatchClassName(): void
    {
        $ts = new TypeSet('DateTime');

        $this->assertTrue($ts->match(new DateTime()));
        $this->assertFalse($ts->match(new stdClass()));
        $this->assertFalse($ts->match('DateTime'));
    }

    /**
     * Test match() with resource type.
     */
    public function testMatchResource(): void
    {
        $ts = new TypeSet('resource');

        $resource = fopen('php://memory', 'r');
        $this->assertIsResource($resource);

        $this->assertTrue($ts->match($resource));
        fclose($resource);

        $this->assertFalse($ts->match(42));
        $this->assertFalse($ts->match('hello'));
    }

    /**
     * Test match() with parent class (inheritance).
     */
    public function testMatchParentClass(): void
    {
        $ts = new TypeSet('Galaxon\Collections\Tests\ParentClass');

        // Child class should match parent class constraint
        $child = new ChildClass();
        $this->assertTrue($ts->match($child));

        // Parent class itself should also match
        $parent = new ParentClass();
        $this->assertTrue($ts->match($parent));
    }

    /**
     * Test match() with trait.
     */
    public function testMatchTrait(): void
    {
        $ts = new TypeSet('Galaxon\Collections\Tests\TestTrait');

        // Class using the trait should match
        $obj = new ClassWithTrait();
        $this->assertTrue($ts->match($obj));

        // Object not using the trait should not match
        $other = new stdClass();
        $this->assertFalse($ts->match($other));
    }

    /**
     * Test match() with interface.
     */
    public function testMatchInterface(): void
    {
        $ts = new TypeSet('Countable');

        // Objects implementing Countable should match
        $this->assertTrue($ts->match(new TypeSet()));
        $this->assertTrue($ts->match(new ArrayObject([1, 2, 3])));

        // Objects not implementing Countable should not match
        $this->assertFalse($ts->match(new stdClass()));
        $this->assertFalse($ts->match('hello'));
        $this->assertFalse($ts->match([1, 2, 3])); // Arrays are countable but not objects
    }

    // endregion

    // region check() method tests

    /**
     * Test check() passes for valid type.
     */
    public function testCheckPassesForValidType(): void
    {
        $ts = new TypeSet('int');

        // Should not throw
        $ts->check(42);
        $this->assertTrue(true); // Test passed if no exception
    }

    /**
     * Test check() throws TypeError for invalid type.
     */
    public function testCheckThrowsTypeErrorForInvalidType(): void
    {
        $ts = new TypeSet('int');

        $this->expectException(TypeError::class);
        $ts->check('hello');
    }

    /**
     * Test check() with custom label.
     */
    public function testCheckWithCustomLabel(): void
    {
        $ts = new TypeSet('int');

        try {
            $ts->check('hello', 'value');
            $this->fail('Expected TypeError was not thrown');
        } catch (TypeError $e) {
            $this->assertStringContainsString('value', $e->getMessage());
        }
    }

    // endregion

    // region tryInferDefaultValue() method tests

    /**
     * Test tryInferDefaultValue() for null.
     */
    public function testTryInferDefaultValueForNull(): void
    {
        $ts = new TypeSet('?int');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertTrue($result);
        $this->assertNull($default);
    }

    /**
     * Test tryInferDefaultValue() for bool.
     */
    public function testTryInferDefaultValueForBool(): void
    {
        $ts = new TypeSet('bool');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertTrue($result);
        $this->assertFalse($default);
    }

    /**
     * Test tryInferDefaultValue() for int.
     */
    public function testTryInferDefaultValueForInt(): void
    {
        $ts = new TypeSet('int');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertTrue($result);
        $this->assertSame(0, $default);
    }

    /**
     * Test tryInferDefaultValue() for uint.
     */
    public function testTryInferDefaultValueForUint(): void
    {
        $ts = new TypeSet('uint');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertTrue($result);
        $this->assertSame(0, $default);
    }

    /**
     * Test tryInferDefaultValue() for number.
     */
    public function testTryInferDefaultValueForNumber(): void
    {
        $ts = new TypeSet('number');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertTrue($result);
        $this->assertSame(0, $default);
    }

    /**
     * Test tryInferDefaultValue() for scalar.
     */
    public function testTryInferDefaultValueForScalar(): void
    {
        $ts = new TypeSet('scalar');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertTrue($result);
        $this->assertSame(0, $default);
    }

    /**
     * Test tryInferDefaultValue() for float.
     */
    public function testTryInferDefaultValueForFloat(): void
    {
        $ts = new TypeSet('float');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertTrue($result);
        $this->assertSame(0.0, $default);
    }

    /**
     * Test tryInferDefaultValue() for string.
     */
    public function testTryInferDefaultValueForString(): void
    {
        $ts = new TypeSet('string');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertTrue($result);
        $this->assertSame('', $default);
    }

    /**
     * Test tryInferDefaultValue() for array.
     */
    public function testTryInferDefaultValueForArray(): void
    {
        $ts = new TypeSet('array');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertTrue($result);
        $this->assertSame([], $default);
    }

    /**
     * Test tryInferDefaultValue() for iterable.
     */
    public function testTryInferDefaultValueForIterable(): void
    {
        $ts = new TypeSet('iterable');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertTrue($result);
        $this->assertSame([], $default);
    }

    /**
     * Test tryInferDefaultValue() returns false for object types.
     */
    public function testTryInferDefaultValueReturnsFalseForObject(): void
    {
        $ts = new TypeSet('DateTime');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertFalse($result);
    }

    /**
     * Test tryInferDefaultValue() returns false for callable.
     */
    public function testTryInferDefaultValueReturnsFalseForCallable(): void
    {
        $ts = new TypeSet('callable');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertFalse($result);
    }

    /**
     * Test tryInferDefaultValue() priority: null has highest priority.
     */
    public function testTryInferDefaultValuePriorityNull(): void
    {
        $ts = new TypeSet('int|string|null');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertTrue($result);
        $this->assertNull($default); // null has priority
    }

    /**
     * Test tryInferDefaultValue() priority: bool before int.
     */
    public function testTryInferDefaultValuePriorityBool(): void
    {
        $ts = new TypeSet('int|bool|string');

        $result = $ts->tryInferDefaultValue($default);

        $this->assertTrue($result);
        $this->assertFalse($default); // bool has priority over int
    }

    // endregion

    // region contains(), containsAll(), containsAny(), containsOnly() tests

    /**
     * Test contains() method.
     */
    public function testContains(): void
    {
        $ts = new TypeSet('int|string');

        $this->assertTrue($ts->contains('int'));
        $this->assertTrue($ts->contains('string'));
        $this->assertFalse($ts->contains('bool'));
    }

    /**
     * Test containsAll() method.
     */
    public function testContainsAll(): void
    {
        $ts = new TypeSet('int|string|bool');

        $this->assertTrue($ts->containsAll('int', 'string'));
        $this->assertTrue($ts->containsAll('int', 'string', 'bool'));
        $this->assertFalse($ts->containsAll('int', 'string', 'float'));
    }

    /**
     * Test containsAny() method.
     */
    public function testContainsAny(): void
    {
        $ts = new TypeSet('int|string');

        $this->assertTrue($ts->containsAny('int', 'bool'));
        $this->assertTrue($ts->containsAny('string', 'float'));
        $this->assertFalse($ts->containsAny('bool', 'float'));
    }

    /**
     * Test containsOnly() method.
     */
    public function testContainsOnly(): void
    {
        $ts = new TypeSet('int|string');

        $this->assertTrue($ts->containsOnly('int', 'string'));
        $this->assertTrue($ts->containsOnly('string', 'int')); // Order doesn't matter
        $this->assertFalse($ts->containsOnly('int')); // Missing string
        $this->assertFalse($ts->containsOnly('int', 'string', 'bool')); // Extra type
    }

    // endregion

    // region empty(), anyOk(), nullOk() tests

    /**
     * Test empty() method.
     */
    public function testEmpty(): void
    {
        $ts1 = new TypeSet();
        $this->assertTrue($ts1->empty());

        $ts2 = new TypeSet('int');
        $this->assertFalse($ts2->empty());
    }

    /**
     * Test anyOk() returns true for empty TypeSet.
     */
    public function testAnyOkForEmptyTypeSet(): void
    {
        $ts = new TypeSet();

        $this->assertTrue($ts->anyOk());
    }

    /**
     * Test anyOk() returns true for mixed type.
     */
    public function testAnyOkForMixed(): void
    {
        $ts = new TypeSet('mixed');

        $this->assertTrue($ts->anyOk());
    }

    /**
     * Test anyOk() returns false for specific types.
     */
    public function testAnyOkForSpecificTypes(): void
    {
        $ts = new TypeSet('int|string');

        $this->assertFalse($ts->anyOk());
    }

    /**
     * Test nullOk() returns true for nullable types.
     */
    public function testNullOkForNullableType(): void
    {
        $ts = new TypeSet('?int');

        $this->assertTrue($ts->nullOk());
    }

    /**
     * Test nullOk() returns true for empty TypeSet.
     */
    public function testNullOkForEmptyTypeSet(): void
    {
        $ts = new TypeSet();

        $this->assertTrue($ts->nullOk());
    }

    /**
     * Test nullOk() returns true for mixed.
     */
    public function testNullOkForMixed(): void
    {
        $ts = new TypeSet('mixed');

        $this->assertTrue($ts->nullOk());
    }

    /**
     * Test nullOk() returns false for non-nullable types.
     */
    public function testNullOkForNonNullableTypes(): void
    {
        $ts = new TypeSet('int|string');

        $this->assertFalse($ts->nullOk());
    }

    // endregion

    // region count(), __toString(), getIterator() tests

    /**
     * Test count() method.
     */
    public function testCount(): void
    {
        $ts1 = new TypeSet();
        $this->assertSame(0, $ts1->count());

        $ts2 = new TypeSet('int');
        $this->assertSame(1, $ts2->count());

        $ts3 = new TypeSet('int|string|bool');
        $this->assertSame(3, $ts3->count());
    }

    /**
     * Test __toString() method.
     */
    public function testToString(): void
    {
        $ts1 = new TypeSet();
        $this->assertSame('{}', (string)$ts1);

        $ts2 = new TypeSet('int');
        $this->assertSame('{int}', (string)$ts2);

        $ts3 = new TypeSet('int|string');
        $str = (string)$ts3;
        // Order might vary, so check both possibilities
        $this->assertTrue($str === '{int, string}' || $str === '{string, int}');
    }

    /**
     * Test getIterator() method.
     */
    public function testGetIterator(): void
    {
        $ts = new TypeSet('int|string|bool');

        $types = [];
        foreach ($ts as $type) {
            $types[] = $type;
        }

        $this->assertCount(3, $types);
        $this->assertContains('int', $types);
        $this->assertContains('string', $types);
        $this->assertContains('bool', $types);
    }

    // endregion
}
