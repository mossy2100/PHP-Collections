<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Dictionary;

use BadMethodCallException;
use InvalidArgumentException;
use Mossy2100\Collections\Collection;
use Mossy2100\Collections\Dictionary;
use Mossy2100\Collections\Pair;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for Dictionary modification methods.
 */
#[CoversClass(Dictionary::class)]
#[CoversClass(Collection::class)]
class DictionaryModificationTest extends TestCase
{
    #region Method add() tests.

    /**
     * Test adding with two parameters (key and value).
     */
    public function testAddWithTwoParameters(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test adding a pair.
        $result = $dict->add('key1', 123);

        // Test fluent interface.
        $this->assertSame($dict, $result);

        // Test the item was added.
        $this->assertCount(1, $dict);
        $this->assertEquals(123, $dict['key1']);
    }

    /**
     * Test adding with one parameter (Pair).
     */
    public function testAddWithPair(): void
    {
        $dict = new Dictionary('string', 'int');
        $pair = new Pair('key1', 123);

        // Test adding the pair.
        $dict->add($pair);

        // Test the item was added.
        $this->assertCount(1, $dict);
        $this->assertEquals(123, $dict['key1']);
    }

    /**
     * Test adding with invalid single parameter throws InvalidArgumentException.
     */
    public function testAddWithInvalidSingleParameter(): void
    {
        $dict = new Dictionary();

        // Test adding with an invalid single parameter throws InvalidArgumentException.
        $this->expectException(InvalidArgumentException::class);
        $dict->add('invalid');
    }

    /**
     * Test adding with three parameters throws BadMethodCallException.
     */
    public function testAddWithThreeParameters(): void
    {
        $dict = new Dictionary();

        // Test adding with three parameters throws BadMethodCallException.
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Invalid argument count: 3. Must be 1 or 2.');
        $dict->add('key', 'value', 'extra');
    }

    /**
     * Test adding with invalid key type throws InvalidArgumentException.
     */
    public function testAddWithInvalidKeyType(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test adding with invalid key type throws InvalidArgumentException.
        $this->expectException(InvalidArgumentException::class);
        $dict->add(123, 456);
    }

    /**
     * Test adding with invalid value type throws InvalidArgumentException.
     */
    public function testAddWithInvalidValueType(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test adding with invalid value type throws InvalidArgumentException.
        $this->expectException(InvalidArgumentException::class);
        $dict->add('key', 'not an int');
    }

    /**
     * Test adding multiple items in a chain.
     */
    public function testAddChaining(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test chaining add calls.
        $dict->add('a', 1)
            ->add('b', 2)
            ->add('c', 3);

        // Test all items were added.
        $this->assertCount(3, $dict);
        $this->assertEquals(1, $dict['a']);
        $this->assertEquals(2, $dict['b']);
        $this->assertEquals(3, $dict['c']);
    }

    /**
     * Test adding a duplicate key replaces the existing value.
     */
    public function testAddDuplicateKeyReplacesValue(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('key', 123);

        // Test replacing the value.
        $dict->add('key', 456);

        // Test only one item exists with the new value.
        $this->assertCount(1, $dict);
        $this->assertEquals(456, $dict['key']);
    }

    /**
     * Test the message when the one-argument form isn't given a Pair.
     */
    public function testAddNonPairMessage(): void
    {
        $dict = new Dictionary();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument type: int. Must be Pair.');
        $dict->add(5);
    }

    /**
     * Test the message for a key of a disallowed type.
     */
    public function testKeyTypeMessage(): void
    {
        $dict = new Dictionary('string', 'int');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid key type: float. Must be string.');
        $dict->add(1.5, 1);
    }

    #endregion

    #region Method import() tests.

    /**
     * Test import method with array.
     */
    public function testImportFromArray(): void
    {
        // Test: Import items from an array
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $result = $dict->import(['b' => 2, 'c' => 3]);

        // Test: Verify items were imported
        $this->assertSame($dict, $result); // Returns $this for chaining
        $this->assertCount(3, $dict);
        $this->assertSame(1, $dict['a']);
        $this->assertSame(2, $dict['b']);
        $this->assertSame(3, $dict['c']);
    }

    /**
     * Test import method with empty iterable.
     */
    public function testImportFromEmptyIterable(): void
    {
        // Test: Import from empty array
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->import([]);

        // Test: Verify dictionary unchanged
        $this->assertCount(1, $dict);
        $this->assertSame(1, $dict['a']);
    }

    /**
     * Test import method from another Dictionary.
     */
    public function testImportFromAnotherDictionary(): void
    {
        // Test: Import from another Dictionary
        $dict1 = new Dictionary('string', 'int');
        $dict1->add('a', 1);

        $dict2 = new Dictionary('string', 'int');
        $dict2->add('b', 2);
        $dict2->add('c', 3);

        $dict1->import($dict2);

        // Test: Verify items were imported
        $this->assertCount(3, $dict1);
        $this->assertSame(1, $dict1['a']);
        $this->assertSame(2, $dict1['b']);
        $this->assertSame(3, $dict1['c']);

        // Test: Original dictionary unchanged
        $this->assertCount(2, $dict2);
    }

    /**
     * Test import preserves existing items.
     */
    public function testImportPreservesExistingItems(): void
    {
        // Test: Verify import doesn't clear existing items
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->import(['c' => 3, 'd' => 4]);

        // Test: Verify existing items preserved
        $this->assertCount(4, $dict);
        $this->assertSame(1, $dict['a']);
        $this->assertSame(2, $dict['b']);
        $this->assertSame(3, $dict['c']);
        $this->assertSame(4, $dict['d']);
    }

    /**
     * Test import can be chained.
     */
    public function testImportCanBeChained(): void
    {
        // Test: Chain multiple import calls
        $dict = new Dictionary('string', 'int');
        $dict->import(['a' => 1, 'b' => 2])
            ->import(['c' => 3, 'd' => 4])
            ->import(['e' => 5, 'f' => 6]);

        // Test: Verify all items imported
        $this->assertCount(6, $dict);
        $this->assertSame(1, $dict['a']);
        $this->assertSame(6, $dict['f']);
    }

    /**
     * Test import throws InvalidArgumentException for invalid value types.
     */
    public function testImportThrowsInvalidArgumentExceptionForInvalidValueTypes(): void
    {
        // Test: Attempt to import items with wrong value type
        $this->expectException(InvalidArgumentException::class);

        $dict = new Dictionary('string', 'int');
        $dict->import(['key' => 'invalid']); // String value, int expected
    }

    /**
     * Test import throws InvalidArgumentException for invalid key types.
     */
    public function testImportThrowsInvalidArgumentExceptionForInvalidKeyTypes(): void
    {
        // Test: Attempt to import items with wrong key type
        $this->expectException(InvalidArgumentException::class);

        $dict = new Dictionary('string', 'int');
        $dict->import([123 => 1]); // Int key, string expected
    }

    /**
     * Test import with overlapping keys updates values.
     */
    public function testImportWithOverlappingKeysUpdatesValues(): void
    {
        // Test: Import items with keys that already exist
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);

        $dict->import(['b' => 20, 'c' => 3]);

        // Test: Verify overlapping key has updated value
        $this->assertCount(3, $dict);
        $this->assertSame(1, $dict['a']);
        $this->assertSame(20, $dict['b']); // Updated value
        $this->assertSame(3, $dict['c']);
    }

    /**
     * Test import with mixed types in untyped Dictionary.
     */
    public function testImportWithMixedTypesInUntypedDictionary(): void
    {
        // Test: Import mixed types into untyped Dictionary
        // Use another Dictionary as source since PHP arrays can't have bool/null keys
        $source = new Dictionary();
        $source->add('string_key', 'value');
        $source->add(123, 456);
        $source->add(true, false);
        $source->add(null, null);

        $dict = new Dictionary();
        $dict->import($source);

        // Test: Verify all types accepted
        $this->assertCount(4, $dict);
        $this->assertSame('value', $dict['string_key']);
        $this->assertSame(456, $dict[123]);
        $this->assertFalse($dict[true]);
        $this->assertNull($dict[null]);
    }

    /**
     * Test import adds nothing if any value has a disallowed type.
     */
    public function testImportIsAllOrNothing(): void
    {
        // Test: Verify nothing is imported if any value is invalid
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        try {
            $dict->import(['b' => 2, 'c' => 'invalid', 'd' => 4]);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (InvalidArgumentException) {
            // Test: Verify the Dictionary is unchanged
            $this->assertCount(1, $dict);
            $this->assertFalse($dict->keyExists('b'));
            $this->assertFalse($dict->keyExists('c'));
            $this->assertFalse($dict->keyExists('d'));
        }
    }

    /**
     * Test import into Dictionary with union types.
     */
    public function testImportIntoDictionaryWithUnionTypes(): void
    {
        // Test: Import into Dictionary with union type constraints
        $dict = new Dictionary('int|string', 'int|string');
        $dict->import([1 => 'one', 'two' => 2, 3 => 'three', 'four' => 4]);

        // Test: Verify both key and value types accepted
        $this->assertCount(4, $dict);
        $this->assertSame('one', $dict[1]);
        $this->assertSame(2, $dict['two']);
        $this->assertSame('three', $dict[3]);
        $this->assertSame(4, $dict['four']);
    }

    /**
     * Test import into empty Dictionary.
     */
    public function testImportIntoEmptyDictionary(): void
    {
        // Test: Import into a new empty Dictionary
        $dict = new Dictionary('string', 'int');
        $dict->import(['a' => 10, 'b' => 20, 'c' => 30]);

        // Test: Verify items imported
        $this->assertCount(3, $dict);
        $this->assertSame(10, $dict['a']);
        $this->assertSame(30, $dict['c']);
    }

    /**
     * Test import with associative array.
     */
    public function testImportWithAssociativeArray(): void
    {
        // Test: Import from associative array
        $dict = new Dictionary('string', 'string');
        $dict->import(['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com']);

        // Test: Verify all key-value pairs imported
        $this->assertCount(3, $dict);
        $this->assertSame('John', $dict['first_name']);
        $this->assertSame('Doe', $dict['last_name']);
        $this->assertSame('john@example.com', $dict['email']);
    }

    /**
     * Test import maintains insertion order.
     */
    public function testImportMaintainsInsertionOrder(): void
    {
        // Test: Import items and verify order
        $dict = new Dictionary('string', 'int');
        $dict->add('first', 1);
        $dict->import(['second' => 2, 'third' => 3]);

        // Test: Verify order is maintained
        $keys = $dict->keys;
        $this->assertEquals(['first', 'second', 'third'], $keys);
    }

    /**
     * Test import adds nothing if any key has a disallowed type.
     */
    public function testImportIsAllOrNothingForKeys(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict['a'] = 1;
        $source = ['b' => 2, 5 => 3];

        try {
            $dict->import($source);
            $this->fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException) {
            // Test the Dictionary is unchanged.
            $this->assertSame(['a'], $dict->keys);
        }
    }

    /**
     * Test import from a generator with non-scalar keys.
     */
    public function testImportFromGeneratorWithObjectKeys(): void
    {
        $key = new stdClass();
        $generator = (static function () use ($key) {
            yield $key => 1;
            yield 'b' => 2;
        })();

        $dict = new Dictionary(null, 'int');
        $dict->import($generator);

        $this->assertSame(1, $dict[$key]);
        $this->assertSame(2, $dict['b']);
    }

    #endregion

    #region Method removeByKey() tests.

    /**
     * Test removeByKey removes an existing item.
     */
    public function testRemoveByKeyRemovesItem(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test removing an item.
        $result = $dict->removeByKey('a');

        // Test the item was removed.
        $this->assertEquals(1, $result);
        $this->assertCount(1, $dict);
        $this->assertFalse($dict->keyExists('a'));
        $this->assertEquals(2, $dict['b']);
    }

    /**
     * Test removeByKey with non-existent key throws OutOfBoundsException.
     */
    public function testRemoveByKeyNonExistentKey(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        // Test removing a non-existent key throws exception.
        $this->expectException(OutOfBoundsException::class);
        $dict->removeByKey('nonexistent');
    }

    /**
     * Test removeByKey with key with disallowed type throws InvalidArgumentException.
     */
    public function testRemoveByKeyDisallowedType(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        // Test removing a key with an invalid type throws exception.
        $this->expectException(InvalidArgumentException::class);
        $dict->removeByKey(3.14);
    }

    #endregion

    #region Method removeByValue() tests.

    /**
     * Test removeByValue removes items with matching value.
     */
    public function testRemoveByValueRemovesMatchingItems(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 1);

        // Test removing by value.
        $result = $dict->removeByValue(1);

        // Test both items with value 1 were removed.
        $this->assertEquals(2, $result);
        $this->assertCount(1, $dict);
        $this->assertFalse($dict->keyExists('a'));
        $this->assertFalse($dict->keyExists('c'));
        $this->assertEquals(2, $dict['b']);
    }

    /**
     * Test removeByValue with non-existent value does nothing.
     */
    public function testRemoveByValueNonExistentValue(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test removing a non-existent value doesn't throw.
        $dict->removeByValue(999);

        // Test the dictionary is unchanged.
        $this->assertCount(2, $dict);
    }

    /**
     * Test removeByValue with a value of a disallowed type returns 0.
     */
    public function testRemoveByValueWithDisallowedType(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        // Test no exception is thrown and nothing is removed.
        $this->assertSame(0, $dict->removeByValue('1'));
        $this->assertCount(1, $dict);
    }

    /**
     * Test removeByValue removes NAN values.
     */
    public function testRemoveByValueNan(): void
    {
        $dict = new Dictionary('string', 'float');
        $dict['a'] = NAN;
        $dict['b'] = 1.0;
        $dict['c'] = NAN;

        $this->assertSame(2, $dict->removeByValue(NAN));
        $this->assertSame(['b'], $dict->keys);
    }

    /**
     * Test removeByValue distinguishes -0.0 from 0.0.
     */
    public function testRemoveByValueNegativeZero(): void
    {
        $dict = new Dictionary('string', 'float');
        $dict['a'] = 0.0;
        $dict['b'] = -0.0;

        $this->assertSame(1, $dict->removeByValue(-0.0));
        $this->assertSame(['a'], $dict->keys);
    }

    #endregion

    #region Method clear() tests.

    /**
     * Test clear removes all items.
     */
    public function testClearRemovesAllItems(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test clearing the dictionary.
        $result = $dict->clear();

        // Test fluent interface.
        $this->assertSame($dict, $result);

        // Test all items were removed.
        $this->assertCount(0, $dict);
        $this->assertTrue($dict->empty());
    }

    /**
     * Test clear on empty dictionary.
     */
    public function testClearOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test clearing an empty dictionary doesn't throw.
        $dict->clear();

        // Test the dictionary is still empty.
        $this->assertCount(0, $dict);
        $this->assertTrue($dict->empty());
    }

    #endregion
}
