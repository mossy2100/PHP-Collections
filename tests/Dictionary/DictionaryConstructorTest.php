<?php

declare(strict_types=1);

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
     * Test constructor with a source array.
     */
    public function testConstructorWithSourceArray(): void
    {
        $arr = ['a' => 1, 'b' => 2, 'c' => 3];
        $dict = new Dictionary(source: $arr);

        // Test all items were copied.
        $this->assertCount(3, $dict);
        $this->assertEquals(1, $dict['a']);
        $this->assertEquals(2, $dict['b']);
        $this->assertEquals(3, $dict['c']);
    }

    /**
     * Test constructor with an empty source array.
     */
    public function testConstructorWithEmptySourceArray(): void
    {
        $arr = [];
        $dict = new Dictionary(source: $arr);

        // Test the dictionary is empty.
        $this->assertCount(0, $dict);
        $this->assertTrue($dict->empty());
    }

    /**
     * Test constructor infers mixed key and value types.
     */
    public function testConstructorInfersMixedKeyAndValueTypes(): void
    {
        $arr = [
            1     => 'one',
            'two' => 2,
            3     => true
        ];
        $dict = new Dictionary(source: $arr);

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
     * Test constructor with another Dictionary as source.
     */
    public function testConstructorWithDictionaryAsSource(): void
    {
        $original = new Dictionary('string', 'int');
        $original->add('a', 1);
        $original->add('b', 2);

        $copy = new Dictionary(source: $original);

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
     * Test constructor infers types correctly.
     */
    public function testConstructorInfersTypes(): void
    {
        $arr = ['key1' => 10, 'key2' => 20];
        $dict = new Dictionary(source: $arr);

        // Test the dictionary works with the inferred types.
        $this->assertCount(2, $dict);

        // Add another item with the same types should work.
        $dict->add('key3', 30);
        $this->assertCount(3, $dict);
    }

    /**
     * Test constructor with explicit key and value types (not inferred).
     */
    public function testConstructorWithExplicitTypesAndSource(): void
    {
        // Test: Create Dictionary with explicit type constraints
        $arr = ['a' => 1, 'b' => 2];
        $dict = new Dictionary('string', 'int', $arr);

        // Test: Verify type constraints applied
        $this->assertTrue($dict->keyTypes->containsOnly('string'));
        $this->assertTrue($dict->valueTypes->containsOnly('int'));
    }

    /**
     * Test constructor throws TypeError when explicit types don't match values.
     */
    public function testConstructorThrowsTypeErrorForMismatchedExplicitTypes(): void
    {
        // Test: Attempt to create Dictionary with mismatched explicit types
        $this->expectException(TypeError::class);

        $arr = ['a' => 1, 'b' => 2];
        new Dictionary('string', 'string', $arr); // Values are int, not string
    }

    /**
     * Test constructor with null type parameters (any type allowed).
     */
    public function testConstructorWithNullTypeParameters(): void
    {
        // Test: Create Dictionary with null type parameters
        $arr = ['a' => 1, 'b' => 'two'];
        $dict = new Dictionary(null, null, $arr);

        // Test: Verify any types allowed
        $this->assertTrue($dict->keyTypes->anyOk());
        $this->assertTrue($dict->valueTypes->anyOk());
        $this->assertCount(2, $dict);
    }

    /**
     * Test constructor infers nullable key types when null keys present.
     */
    public function testConstructorInfersNullableKeyTypes(): void
    {
        // Test: Create Dictionary with null keys (types inferred)
        // Use another Dictionary as source since PHP arrays can't have null keys
        $source = new Dictionary();
        $source->add('a', 1);
        $source->add(null, 2);
        $source->add('b', 3);

        $dict = new Dictionary(source: $source);

        // Test: Verify all items preserved
        $this->assertCount(3, $dict);
        $this->assertSame(1, $dict['a']);
        $this->assertSame(2, $dict[null]);
        $this->assertSame(3, $dict['b']);

        // Test: Verify both string and null key types were inferred
        $this->assertTrue($dict->keyTypes->containsOnly('string', 'null'));
    }

    /**
     * Test constructor infers nullable value types when null values present.
     */
    public function testConstructorInfersNullableValueTypes(): void
    {
        // Test: Create Dictionary with null values (types inferred)
        $arr = ['a' => 1, 'b' => null, 'c' => 3];
        $dict = new Dictionary(source: $arr);

        // Test: Verify all items preserved
        $this->assertCount(3, $dict);
        $this->assertSame(1, $dict['a']);
        $this->assertNull($dict['b']);
        $this->assertSame(3, $dict['c']);

        // Test: Verify both int and null value types were inferred
        $this->assertTrue($dict->valueTypes->containsOnly('int', 'null'));
    }

    /**
     * Test constructor infers multiple key types correctly.
     */
    public function testConstructorInfersMultipleKeyTypes(): void
    {
        // Test: Create Dictionary with various key types
        // Use another Dictionary as source since PHP arrays can't have bool/null keys
        $source = new Dictionary();
        $source->add('string', 1);
        $source->add(123, 2);
        $source->add(true, 3);
        $source->add(null, 4);

        $dict = new Dictionary(source: $source);

        // Test: Verify all unique key types were inferred
        $this->assertTrue($dict->keyTypes->containsOnly('string', 'int', 'bool', 'null'));
    }

    /**
     * Test constructor infers multiple value types correctly.
     */
    public function testConstructorInfersMultipleValueTypes(): void
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
        $dict = new Dictionary(source: $arr);

        // Test: Verify all unique value types were inferred
        $this->assertTrue($dict->valueTypes->containsOnly('int', 'string', 'float', 'bool', 'null', 'array'));
    }

    /**
     * Test constructor with only null keys.
     */
    public function testConstructorWithOnlyNullKey(): void
    {
        // Test: Create Dictionary with only null key
        // Use another Dictionary as source since PHP arrays can't have null keys
        $source = new Dictionary();
        $source->add(null, 'value');

        $dict = new Dictionary(source: $source);

        // Test: Verify null key type inferred
        $this->assertCount(1, $dict);
        $this->assertTrue($dict->keyTypes->contains('null'));
        $this->assertSame('value', $dict[null]);
    }

    /**
     * Test constructor with only null values.
     */
    public function testConstructorWithOnlyNullValues(): void
    {
        // Test: Create Dictionary containing only nulls
        $arr = ['a' => null, 'b' => null, 'c' => null];
        $dict = new Dictionary(source: $arr);

        // Test: Verify null value type inferred
        $this->assertCount(3, $dict);
        $this->assertTrue($dict->valueTypes->contains('null'));
        $this->assertNull($dict['a']);
        $this->assertNull($dict['c']);
    }

    /**
     * Test constructor with explicit types and type inference disabled.
     */
    public function testConstructorWithExplicitTypesDisablesInference(): void
    {
        // Test: Create Dictionary with explicit types
        $arr = ['a' => 1, 'b' => 2];
        $dict = new Dictionary('string', 'int', $arr);

        // Test: Verify only explicit types, no additional inference
        $this->assertTrue($dict->keyTypes->containsOnly('string'));
        $this->assertTrue($dict->valueTypes->containsOnly('int'));
    }

    /**
     * Test constructor with union type string for keys.
     */
    public function testConstructorWithUnionTypeStringForKeys(): void
    {
        // Test: Create Dictionary with union type for keys
        $arr = [1 => 'one', 'two' => 2];
        $dict = new Dictionary('int|string', 'int|string', $arr);

        // Test: Verify both key types accepted
        $this->assertCount(2, $dict);
        $this->assertTrue($dict->keyTypes->containsOnly('int', 'string'));
    }

    /**
     * Test constructor with generator and type inference.
     */
    public function testConstructorWithGeneratorAndTypeInference(): void
    {
        // Test: Create Dictionary from generator with type inference
        $generator = function () {
            yield 'a' => 10;
            yield 'b' => 20;
            yield 'c' => 30;
        };

        $dict = new Dictionary(source: $generator());

        // Test: Verify items and types
        $this->assertCount(3, $dict);
        $this->assertSame(10, $dict['a']);
        $this->assertSame(30, $dict['c']);
        $this->assertTrue($dict->keyTypes->containsOnly('string'));
        $this->assertTrue($dict->valueTypes->containsOnly('int'));
    }

    /**
     * Test combine() with matching arrays.
     */
    public function testCombineWithMatchingArrays(): void
    {
        $keys = ['a', 'b', 'c'];
        $values = [1, 2, 3];

        $dict = Dictionary::combine($keys, $values);

        // Test: Verify all items combined correctly
        $this->assertCount(3, $dict);
        $this->assertEquals(1, $dict['a']);
        $this->assertEquals(2, $dict['b']);
        $this->assertEquals(3, $dict['c']);
    }

    /**
     * Test combine() infers types correctly.
     */
    public function testCombineInfersTypes(): void
    {
        $keys = ['a', 'b', 'c'];
        $values = [1, 2, 3];

        $dict = Dictionary::combine($keys, $values);

        // Test: Verify types inferred from data
        $this->assertTrue($dict->keyTypes->containsOnly('string'));
        $this->assertTrue($dict->valueTypes->containsOnly('int'));
    }

    /**
     * Test combine() with mixed key types.
     */
    public function testCombineWithMixedKeyTypes(): void
    {
        $keys = [1, 'two', 3];
        $values = ['one', 2, 'three'];

        $dict = Dictionary::combine($keys, $values);

        // Test: Verify all items combined correctly
        $this->assertCount(3, $dict);
        $this->assertEquals('one', $dict[1]);
        $this->assertEquals(2, $dict['two']);
        $this->assertEquals('three', $dict[3]);

        // Test: Verify mixed types inferred
        $this->assertTrue($dict->keyTypes->containsOnly('int', 'string'));
        $this->assertTrue($dict->valueTypes->containsOnly('string', 'int'));
    }

    /**
     * Test combine() with object keys.
     */
    public function testCombineWithObjectKeys(): void
    {
        $obj1 = (object)['id' => 1];
        $obj2 = (object)['id' => 2];
        $keys = [$obj1, $obj2];
        $values = ['first', 'second'];

        $dict = Dictionary::combine($keys, $values);

        // Test: Verify object keys work
        $this->assertCount(2, $dict);
        $this->assertEquals('first', $dict[$obj1]);
        $this->assertEquals('second', $dict[$obj2]);
    }

    /**
     * Test combine() with empty arrays.
     */
    public function testCombineWithEmptyArrays(): void
    {
        $keys = [];
        $values = [];

        $dict = Dictionary::combine($keys, $values);

        // Test: Verify empty dictionary created
        $this->assertCount(0, $dict);
        $this->assertTrue($dict->empty());
    }

    /**
     * Test combine() with iterators.
     */
    public function testCombineWithIterators(): void
    {
        $keysGen = function () {
            yield 'key1';
            yield 'key2';
            yield 'key3';
        };

        $valuesGen = function () {
            yield 10;
            yield 20;
            yield 30;
        };

        $dict = Dictionary::combine($keysGen(), $valuesGen());

        // Test: Verify items combined from iterators
        $this->assertCount(3, $dict);
        $this->assertEquals(10, $dict['key1']);
        $this->assertEquals(20, $dict['key2']);
        $this->assertEquals(30, $dict['key3']);
    }

    /**
     * Test combine() throws ValueError for mismatched counts.
     */
    public function testCombineThrowsValueErrorForMismatchedCounts(): void
    {
        $keys = ['a', 'b', 'c'];
        $values = [1, 2]; // One less value

        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Cannot combine: keys count (3) does not match values count (2).');

        Dictionary::combine($keys, $values);
    }

    /**
     * Test combine() throws ValueError for duplicate keys.
     */
    public function testCombineThrowsValueErrorForDuplicateKeys(): void
    {
        $keys = ['a', 'b', 'a']; // Duplicate 'a'
        $values = [1, 2, 3];

        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Cannot combine: keys are not unique.');

        Dictionary::combine($keys, $values);
    }

    /**
     * Test combine() throws ValueError for duplicate object keys.
     */
    public function testCombineThrowsValueErrorForDuplicateObjectKeys(): void
    {
        $obj = (object)['id' => 1];
        $keys = [$obj, $obj]; // Same object twice
        $values = ['first', 'second'];

        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Cannot combine: keys are not unique.');

        Dictionary::combine($keys, $values);
    }

    /**
     * Test combine() with null keys and values.
     */
    public function testCombineWithNullKeysAndValues(): void
    {
        $keys = ['a', null, 'c'];
        $values = [1, null, 3];

        $dict = Dictionary::combine($keys, $values);

        // Test: Verify null key and value work
        $this->assertCount(3, $dict);
        $this->assertEquals(1, $dict['a']);
        $this->assertNull($dict[null]);
        $this->assertEquals(3, $dict['c']);

        // Test: Verify nullable types inferred
        $this->assertTrue($dict->keyTypes->contains('null'));
        $this->assertTrue($dict->valueTypes->contains('null'));
    }

    /**
     * Test combine() with array keys.
     */
    public function testCombineWithArrayKeys(): void
    {
        $keys = [[1, 2], [3, 4], [5, 6]];
        $values = ['first', 'second', 'third'];

        $dict = Dictionary::combine($keys, $values);

        // Test: Verify array keys work
        $this->assertCount(3, $dict);
        $this->assertEquals('first', $dict[[1, 2]]);
        $this->assertEquals('second', $dict[[3, 4]]);
        $this->assertEquals('third', $dict[[5, 6]]);
    }

    /**
     * Test combine() with type inference disabled.
     */
    public function testCombineWithTypeInferenceDisabled(): void
    {
        $keys = ['a', 'b', 'c'];
        $values = [1, 2, 3];

        $dict = Dictionary::combine($keys, $values, false);

        // Test: Verify items combined correctly
        $this->assertCount(3, $dict);
        $this->assertEquals(1, $dict['a']);
        $this->assertEquals(2, $dict['b']);
        $this->assertEquals(3, $dict['c']);

        // Test: Verify types not inferred (any type allowed)
        $this->assertTrue($dict->keyTypes->anyOk());
        $this->assertTrue($dict->valueTypes->anyOk());

        // Test: Can add different types since inference disabled
        $dict->add(123, 'string value');
        $dict->add(true, [1, 2, 3]);
        $this->assertCount(5, $dict);
    }

    /**
     * Test combine() with type inference explicitly enabled.
     */
    public function testCombineWithTypeInferenceEnabled(): void
    {
        $keys = ['a', 'b'];
        $values = [1, 2];

        $dict = Dictionary::combine($keys, $values, true);

        // Test: Verify types inferred
        $this->assertTrue($dict->keyTypes->containsOnly('string'));
        $this->assertTrue($dict->valueTypes->containsOnly('int'));

        // Test: Adding different types should fail
        $this->expectException(TypeError::class);
        $dict->add(123, 999); // int key not allowed
    }

    /**
     * Test combine() respects inferred types when adding new items.
     */
    public function testCombineInferredTypesEnforced(): void
    {
        $keys = ['a', 'b'];
        $values = [1, 2];

        $dict = Dictionary::combine($keys, $values); // Defaults to infer_types = true

        // Test: Adding matching types works
        $dict->add('c', 3);
        $this->assertCount(3, $dict);

        // Test: Adding wrong value type fails
        $this->expectException(TypeError::class);
        $dict->add('d', 'string'); // string value not allowed
    }
}
