<?php

declare(strict_types=1);

namespace Galaxon\Collections\Tests\Dictionary;

use Galaxon\Collections\Dictionary;
use Galaxon\Collections\KeyValuePair;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

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
    public function testFromIterableWithMixedKeys(): void
    {
        $arr = [
            1 => 'one',
            'two' => 2,
            3.5 => 'three-point-five'
        ];
        $dict = Dictionary::fromIterable($arr);
        
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
}
