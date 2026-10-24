<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Dictionary;

use InvalidArgumentException;
use LengthException;
use Mossy2100\Collections\Dictionary;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Dictionary factory methods.
 */
#[CoversClass(Dictionary::class)]
class DictionaryFactoryTest extends TestCase
{
    #region Method combine() tests.

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
        $obj1 = (object) [
            'id' => 1,
        ];
        $obj2 = (object) [
            'id' => 2,
        ];
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
        $keysGen = static function () {
            yield 'key1';
            yield 'key2';
            yield 'key3';
        };

        $valuesGen = static function () {
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
     * Test combine() throws LengthException for mismatched counts.
     */
    public function testCombineThrowsLengthExceptionForMismatchedCounts(): void
    {
        $keys = ['a', 'b', 'c'];
        $values = [1, 2]; // One less value

        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('Cannot combine due to incorrect values count: 2. Expected 3.');

        Dictionary::combine($keys, $values);
    }

    /**
     * Test combine() throws OutOfBoundsException for duplicate keys.
     */
    public function testCombineThrowsOutOfBoundsExceptionForDuplicateKeys(): void
    {
        $keys = ['a', 'b', 'a']; // Duplicate 'a'
        $values = [1, 2, 3];

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Cannot combine. Keys are not unique.');

        Dictionary::combine($keys, $values);
    }

    /**
     * Test combine() throws OutOfBoundsException for duplicate object keys.
     */
    public function testCombineThrowsOutOfBoundsExceptionForDuplicateObjectKeys(): void
    {
        $obj = (object) [
            'id' => 1,
        ];
        $keys = [$obj, $obj]; // Same object twice
        $values = ['first', 'second'];

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Cannot combine. Keys are not unique.');

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
        $keys = [
            [1, 2],
            [3, 4],
            [5, 6],
        ];
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
        $this->expectException(InvalidArgumentException::class);
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
        $this->expectException(InvalidArgumentException::class);
        $dict->add('d', 'string'); // string value not allowed
    }

    #endregion
}
