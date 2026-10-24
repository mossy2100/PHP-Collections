<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\TypeSet;

use ArrayObject;
use DateTime;
use Mossy2100\Collections\Tests\Fixtures\ChildClass;
use Mossy2100\Collections\Tests\Fixtures\ClassWithTrait;
use Mossy2100\Collections\Tests\Fixtures\ParentClass;
use Mossy2100\Collections\TypeSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for TypeSet inspection methods.
 */
#[CoversClass(TypeSet::class)]
class TypeSetInspectionTest extends TestCase
{
    #region Method match() tests.

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

        $this->assertTrue($ts->match(static fn () => null));
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
        $ts = new TypeSet('Mossy2100\Collections\Tests\Fixtures\ParentClass');

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
        $ts = new TypeSet('Mossy2100\Collections\Tests\Fixtures\TestTrait');

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

    #endregion

    #region Method contains() tests.

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

    #endregion

    #region Method containsAll() tests.

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

    #endregion

    #region Method containsAny() tests.

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

    #endregion

    #region Method containsOnly() tests.

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

    /**
     * Test containsOnly() ignores duplicate arguments, including different spellings of the same type.
     */
    public function testContainsOnlyWithDuplicates(): void
    {
        $this->assertFalse(new TypeSet('int|string')->containsOnly('int', 'int'));
        $this->assertTrue(new TypeSet('int')->containsOnly('int', 'int'));
        $this->assertTrue(new TypeSet('DateTime')->containsOnly('DateTime', '\\DateTime'));
        $this->assertTrue(new TypeSet('int|string')->containsOnly(' int', 'string', 'int'));
    }

    #endregion

    #region Method empty() tests.

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

    #endregion

    #region Method anyOk() tests.

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

    #endregion

    #region Method nullOk() tests.

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

    #endregion

    #region Method count() tests.

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

    #endregion
}
