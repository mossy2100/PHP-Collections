<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Dictionary;

use Galaxon\Collections\Dictionary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Tests for Dictionary constructor and factory methods.
 */
#[CoversClass(Dictionary::class)]
class DictionaryConstructorTest extends TestCase
{
    /**
     * Test creating a Dictionary with no type constraints.
     */
    public function testConstructorWithNoTypes(): void
    {
        $dict = new Dictionary();

        // Test the dictionary is empty.
        $this->assertCount(0, $dict);
        $this->assertTrue($dict->empty());

        // Test any type can be added.
        $dict->add('key1', 123);
        $dict->add(456, 'value');
        $dict->add(true, false);

        $this->assertCount(3, $dict);
    }

    /**
     * Test creating a Dictionary with key type constraint only.
     */
    public function testConstructorWithKeyTypeOnly(): void
    {
        $dict = new Dictionary('string');

        // Test string keys work.
        $dict->add('key1', 123);
        $dict->add('key2', 'value');

        $this->assertCount(2, $dict);
    }

    /**
     * Test creating a Dictionary with both key and value type constraints.
     */
    public function testConstructorWithBothTypes(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test adding valid types.
        $dict->add('key1', 123);
        $dict->add('key2', 456);

        $this->assertCount(2, $dict);
        $this->assertEquals(123, $dict['key1']);
        $this->assertEquals(456, $dict['key2']);
    }

    /**
     * Test creating a Dictionary with union key types.
     */
    public function testConstructorWithUnionKeyTypes(): void
    {
        $dict = new Dictionary('int|string', 'mixed');

        // Check the key typeset includes both types.
        $this->assertTrue($dict->keyTypes->containsOnly('int', 'string'));

        // Test both int and string keys work.
        $dict->add(1, 'value1');
        $dict->add('key2', 'value2');

        $this->assertCount(2, $dict);
    }

    /**
     * Test creating a Dictionary with union value types.
     */
    public function testConstructorWithUnionValueTypes(): void
    {
        $dict = new Dictionary('string', 'int|float|string');

        // Check the value typeset includes all types.
        $this->assertTrue($dict->valueTypes->containsOnly('int', 'float', 'string'));

        // Test all value types work.
        $dict->add('key1', 123);
        $dict->add('key2', 45.67);
        $dict->add('key3', 'text');

        $this->assertCount(3, $dict);
    }

    /**
     * Test creating a Dictionary with nullable key type.
     */
    public function testConstructorWithNullableKeyType(): void
    {
        $dict = new Dictionary('?string', 'int');

        // Check the key typeset includes both types.
        $this->assertTrue($dict->keyTypes->containsOnly('null', 'string'));

        // Test null key works.
        $dict->add(null, 123);
        $dict->add('key', 456);

        $this->assertCount(2, $dict);
        $this->assertEquals(123, $dict[null]);
    }

    /**
     * Test creating a Dictionary with nullable value type.
     */
    public function testConstructorWithNullableValueType(): void
    {
        $dict = new Dictionary('string', '?int');

        // Check the value typeset includes both types.
        $this->assertTrue($dict->valueTypes->containsOnly('null', 'int'));

        // Test null value works.
        $dict->add('key1', null);
        $dict->add('key2', 456);

        $this->assertCount(2, $dict);
        $this->assertNull($dict['key1']);
    }

    /**
     * Test fromIterable with a plain PHP array.
     */
    public function testFromIterableWithArray(): void
    {
        $arr = ['a' => 1, 'b' => 2, 'c' => 3];
        $dict = Dictionary::fromIterable($arr);

        // Test all items were copied.
        $this->assertCount(3, $dict);
        $this->assertEquals(1, $dict['a']);
        $this->assertEquals(2, $dict['b']);
        $this->assertEquals(3, $dict['c']);
    }

    /**
     * Test fromIterable with an empty array.
     */
    public function testFromIterableWithEmptyArray(): void
    {
        $arr = [];
        $dict = Dictionary::fromIterable($arr);

        // Test the dictionary is empty.
        $this->assertCount(0, $dict);
        $this->assertTrue($dict->empty());
    }

    /**
     * Test fromIterable with mixed key types.
     */
    public function testFromIterableWithMixedKeyAndValueTypes(): void
    {
        $arr = [
            1     => 'one',
            'two' => 2,
            3     => true
        ];
        $dict = Dictionary::fromIterable($arr);

        // Check the key typeset includes both types.
        $this->assertTrue($dict->keyTypes->containsOnly('string', 'int'));

        // Check the value typeset includes both types.
        $this->assertTrue($dict->valueTypes->containsOnly('string', 'int', 'bool'));

        // Test all items were copied.
        $this->assertCount(3, $dict);
        $this->assertEquals('one', $dict[1]);
        $this->assertEquals(2, $dict['two']);
    }

    /**
     * Test fromIterable with another Dictionary.
     */
    public function testFromIterableWithDictionary(): void
    {
        $original = new Dictionary('string', 'int');
        $original->add('a', 1);
        $original->add('b', 2);

        $copy = Dictionary::fromIterable($original);

        // Test all items were copied.
        $this->assertCount(2, $copy);
        $this->assertEquals(1, $copy['a']);
        $this->assertEquals(2, $copy['b']);

        // Test they are separate instances.
        $original->add('c', 3);
        $this->assertCount(3, $original);
        $this->assertCount(2, $copy);
    }

    /**
     * Test fromIterable infers types correctly.
     */
    public function testFromIterableInfersTypes(): void
    {
        $arr = ['key1' => 10, 'key2' => 20];
        $dict = Dictionary::fromIterable($arr);

        // Test the dictionary works with the inferred types.
        $this->assertCount(2, $dict);

        // Add another item with the same types should work.
        $dict->add('key3', 30);
        $this->assertCount(3, $dict);
    }

    /**
     * Test fromIterable with explicit key and value types (not inferred).
     */
    public function testFromIterableWithExplicitTypes(): void
    {
        // Test: Create Dictionary with explicit type constraints
        $arr = ['a' => 1, 'b' => 2];
        $dict = Dictionary::fromIterable($arr, 'string', 'int');

        // Test: Verify type constraints applied
        $this->assertTrue($dict->keyTypes->containsOnly('string'));
        $this->assertTrue($dict->valueTypes->containsOnly('int'));
    }

    /**
     * Test fromIterable throws TypeError when explicit types don't match values.
     */
    public function testFromIterableThrowsTypeErrorForMismatchedExplicitTypes(): void
    {
        // Test: Attempt to create Dictionary with mismatched explicit types
        $this->expectException(TypeError::class);

        $arr = ['a' => 1, 'b' => 2];
        Dictionary::fromIterable($arr, 'string', 'string'); // Values are int, not string
    }

    /**
     * Test fromIterable with null type parameters (any type allowed).
     */
    public function testFromIterableWithNullTypeParameters(): void
    {
        // Test: Create Dictionary with null type parameters
        $arr = ['a' => 1, 'b' => 'two'];
        $dict = Dictionary::fromIterable($arr, null, null);

        // Test: Verify any types allowed
        $this->assertTrue($dict->keyTypes->anyOk());
        $this->assertTrue($dict->valueTypes->anyOk());
        $this->assertCount(2, $dict);
    }

    /**
     * Test fromIterable infers nullable key types when null keys present.
     */
    public function testFromIterableInfersNullableKeyTypes(): void
    {
        // Test: Create Dictionary with null keys (types inferred)
        // Use another Dictionary as source since PHP arrays can't have null keys
        $source = new Dictionary();
        $source->add('a', 1);
        $source->add(null, 2);
        $source->add('b', 3);

        $dict = Dictionary::fromIterable($source);

        // Test: Verify all items preserved
        $this->assertCount(3, $dict);
        $this->assertSame(1, $dict['a']);
        $this->assertSame(2, $dict[null]);
        $this->assertSame(3, $dict['b']);

        // Test: Verify both string and null key types were inferred
        $this->assertTrue($dict->keyTypes->containsOnly('string', 'null'));
    }

    /**
     * Test fromIterable infers nullable value types when null values present.
     */
    public function testFromIterableInfersNullableValueTypes(): void
    {
        // Test: Create Dictionary with null values (types inferred)
        $arr = ['a' => 1, 'b' => null, 'c' => 3];
        $dict = Dictionary::fromIterable($arr);

        // Test: Verify all items preserved
        $this->assertCount(3, $dict);
        $this->assertSame(1, $dict['a']);
        $this->assertNull($dict['b']);
        $this->assertSame(3, $dict['c']);

        // Test: Verify both int and null value types were inferred
        $this->assertTrue($dict->valueTypes->containsOnly('int', 'null'));
    }

    /**
     * Test fromIterable infers multiple key types correctly.
     */
    public function testFromIterableInfersMultipleKeyTypes(): void
    {
        // Test: Create Dictionary with various key types
        // Use another Dictionary as source since PHP arrays can't have bool/null keys
        $source = new Dictionary();
        $source->add('string', 1);
        $source->add(123, 2);
        $source->add(true, 3);
        $source->add(null, 4);

        $dict = Dictionary::fromIterable($source);

        // Test: Verify all unique key types were inferred
        $this->assertTrue($dict->keyTypes->containsOnly('string', 'int', 'bool', 'null'));
    }

    /**
     * Test fromIterable infers multiple value types correctly.
     */
    public function testFromIterableInfersMultipleValueTypes(): void
    {
        // Test: Create Dictionary with various value types
        $arr = [
            'a' => 1,
            'b' => 'hello',
            'c' => 3.14,
            'd' => true,
            'e' => null,
            'f' => []
        ];
        $dict = Dictionary::fromIterable($arr);

        // Test: Verify all unique value types were inferred
        $this->assertTrue($dict->valueTypes->containsOnly('int', 'string', 'float', 'bool', 'null', 'array'));
    }

    /**
     * Test fromIterable with only null keys.
     */
    public function testFromIterableWithOnlyNullKey(): void
    {
        // Test: Create Dictionary with only null key
        // Use another Dictionary as source since PHP arrays can't have null keys
        $source = new Dictionary();
        $source->add(null, 'value');

        $dict = Dictionary::fromIterable($source);

        // Test: Verify null key type inferred
        $this->assertCount(1, $dict);
        $this->assertTrue($dict->keyTypes->contains('null'));
        $this->assertSame('value', $dict[null]);
    }

    /**
     * Test fromIterable with only null values.
     */
    public function testFromIterableWithOnlyNullValues(): void
    {
        // Test: Create Dictionary containing only nulls
        $arr = ['a' => null, 'b' => null, 'c' => null];
        $dict = Dictionary::fromIterable($arr);

        // Test: Verify null value type inferred
        $this->assertCount(3, $dict);
        $this->assertTrue($dict->valueTypes->contains('null'));
        $this->assertNull($dict['a']);
        $this->assertNull($dict['c']);
    }

    /**
     * Test fromIterable with explicit types and type inference disabled.
     */
    public function testFromIterableWithExplicitTypesDisablesInference(): void
    {
        // Test: Create Dictionary with explicit types
        $arr = ['a' => 1, 'b' => 2];
        $dict = Dictionary::fromIterable($arr, 'string', 'int');

        // Test: Verify only explicit types, no additional inference
        $this->assertTrue($dict->keyTypes->containsOnly('string'));
        $this->assertTrue($dict->valueTypes->containsOnly('int'));
    }

    /**
     * Test fromIterable with union type string for keys.
     */
    public function testFromIterableWithUnionTypeStringForKeys(): void
    {
        // Test: Create Dictionary with union type for keys
        $arr = [1 => 'one', 'two' => 2];
        $dict = Dictionary::fromIterable($arr, 'int|string', 'int|string');

        // Test: Verify both key types accepted
        $this->assertCount(2, $dict);
        $this->assertTrue($dict->keyTypes->containsOnly('int', 'string'));
    }

    /**
     * Test fromIterable with generator and type inference.
     */
    public function testFromIterableWithGeneratorAndTypeInference(): void
    {
        // Test: Create Dictionary from generator with type inference
        $generator = function () {
            yield 'a' => 10;
            yield 'b' => 20;
            yield 'c' => 30;
        };

        $dict = Dictionary::fromIterable($generator());

        // Test: Verify items and types
        $this->assertCount(3, $dict);
        $this->assertSame(10, $dict['a']);
        $this->assertSame(30, $dict['c']);
        $this->assertTrue($dict->keyTypes->containsOnly('string'));
        $this->assertTrue($dict->valueTypes->containsOnly('int'));
    }
}
