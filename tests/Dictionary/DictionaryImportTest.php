<?php

declare(strict_types=1);

namespace Galaxon\Collections\Tests\Dictionary;

use Galaxon\Collections\Dictionary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Tests for Dictionary import() method.
 */
#[CoversClass(Dictionary::class)]
class DictionaryImportTest extends TestCase
{
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
     * Test import throws TypeError for invalid value types.
     */
    public function testImportThrowsTypeErrorForInvalidValueTypes(): void
    {
        // Test: Attempt to import items with wrong value type
        $this->expectException(TypeError::class);

        $dict = new Dictionary('string', 'int');
        $dict->import(['key' => 'invalid']); // String value, int expected
    }

    /**
     * Test import throws TypeError for invalid key types.
     */
    public function testImportThrowsTypeErrorForInvalidKeyTypes(): void
    {
        // Test: Attempt to import items with wrong key type
        $this->expectException(TypeError::class);

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
     * Test import stops on first type error.
     */
    public function testImportStopsOnFirstTypeError(): void
    {
        // Test: Verify import stops at first invalid type
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        try {
            $dict->import(['b' => 2, 'c' => 'invalid', 'd' => 4]);
            $this->fail('Expected TypeError was not thrown');
        } catch (TypeError $e) {
            // Test: Verify only valid items before error were imported
            $this->assertCount(2, $dict); // 'a', 'b'
            $this->assertSame(2, $dict['b']);
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
        $dict->import([
            1 => 'one',
            'two' => 2,
            3 => 'three',
            'four' => 4
        ]);

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
        $dict->import([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com'
        ]);

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
        $keys = $dict->keys();
        $this->assertEquals(['first', 'second', 'third'], $keys);
    }
}
