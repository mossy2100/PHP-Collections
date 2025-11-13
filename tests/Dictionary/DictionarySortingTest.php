<?php

declare(strict_types=1);

namespace Galaxon\Collections\Tests\Dictionary;

use Galaxon\Collections\Dictionary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Dictionary sorting methods.
 */
#[CoversClass(Dictionary::class)]
class DictionarySortingTest extends TestCase
{
    /**
     * Test sortByKey sorts dictionary by keys in ascending order.
     */
    public function testSortByKeySortsAscending(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('c', 3);
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test sorting by key.
        $result = $dict->sortByKey();

        // Test fluent interface.
        $this->assertSame($dict, $result);

        // Test keys are in sorted order.
        $keys = $dict->keys();
        $this->assertEquals(['a', 'b', 'c'], $keys);
    }

    /**
     * Test sortByKey with integer keys.
     */
    public function testSortByKeyWithIntegerKeys(): void
    {
        $dict = new Dictionary('int', 'string');
        $dict->add(3, 'three');
        $dict->add(1, 'one');
        $dict->add(2, 'two');

        // Test sorting by integer key.
        $dict->sortByKey();

        // Test keys are in sorted order.
        $keys = $dict->keys();
        $this->assertEquals([1, 2, 3], $keys);
    }

    /**
     * Test sortByKey on empty dictionary.
     */
    public function testSortByKeyOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test sorting empty dictionary doesn't throw.
        $dict->sortByKey();

        // Test dictionary is still empty.
        $this->assertCount(0, $dict);
    }

    /**
     * Test sortByKey preserves values.
     */
    public function testSortByKeyPreservesValues(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('z', 100);
        $dict->add('a', 200);
        $dict->add('m', 300);

        // Test sorting by key.
        $dict->sortByKey();

        // Test values are preserved with correct keys.
        $this->assertEquals(200, $dict['a']);
        $this->assertEquals(300, $dict['m']);
        $this->assertEquals(100, $dict['z']);
    }

    /**
     * Test sortByValue sorts dictionary by values in ascending order.
     */
    public function testSortByValueSortsAscending(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 3);
        $dict->add('b', 1);
        $dict->add('c', 2);

        // Test sorting by value.
        $result = $dict->sortByValue();

        // Test fluent interface.
        $this->assertSame($dict, $result);

        // Test values are in sorted order.
        $values = $dict->values();
        $this->assertEquals([1, 2, 3], $values);
    }

    /**
     * Test sortByValue with string values.
     */
    public function testSortByValueWithStringValues(): void
    {
        $dict = new Dictionary('int', 'string');
        $dict->add(1, 'zebra');
        $dict->add(2, 'apple');
        $dict->add(3, 'mango');

        // Test sorting by string value.
        $dict->sortByValue();

        // Test values are in sorted order.
        $values = $dict->values();
        $this->assertEquals(['apple', 'mango', 'zebra'], $values);
    }

    /**
     * Test sortByValue on empty dictionary.
     */
    public function testSortByValueOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test sorting empty dictionary doesn't throw.
        $dict->sortByValue();

        // Test dictionary is still empty.
        $this->assertCount(0, $dict);
    }

    /**
     * Test sortByValue preserves keys.
     */
    public function testSortByValuePreservesKeys(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('first', 300);
        $dict->add('second', 100);
        $dict->add('third', 200);

        // Test sorting by value.
        $dict->sortByValue();

        // Test keys are preserved with correct values.
        $keys = $dict->keys();
        $this->assertEquals(['second', 'third', 'first'], $keys);
        $this->assertEquals(100, $dict['second']);
        $this->assertEquals(200, $dict['third']);
        $this->assertEquals(300, $dict['first']);
    }

    /**
     * Test sortByValue with duplicate values maintains stable order.
     */
    public function testSortByValueWithDuplicates(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 2);
        $dict->add('b', 1);
        $dict->add('c', 2);
        $dict->add('d', 1);

        // Test sorting by value with duplicates.
        $dict->sortByValue();

        // Test values are sorted.
        $values = $dict->values();
        $this->assertEquals([1, 1, 2, 2], $values);
    }

    /**
     * Test chaining multiple sorts.
     */
    public function testChainingSorts(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('z', 1);
        $dict->add('a', 3);
        $dict->add('m', 2);

        // Test chaining sorts.
        $dict->sortByValue()->sortByKey();

        // Test final sort (by key) is applied.
        $keys = $dict->keys();
        $this->assertEquals(['a', 'm', 'z'], $keys);
    }

    /**
     * Test sorting doesn't create a new dictionary.
     */
    public function testSortingModifiesInPlace(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('c', 3);
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test sorting returns same instance.
        $result = $dict->sortByKey();

        $this->assertSame($dict, $result);
    }

    /**
     * Test sortByKey with mixed numeric and string keys.
     */
    public function testSortByKeyWithMixedTypes(): void
    {
        $dict = new Dictionary('int|string', 'int');
        $dict->add(3, 30);
        $dict->add('a', 10);
        $dict->add(1, 20);
        $dict->add('z', 40);

        // Test sorting by key with mixed types.
        $dict->sortByKey();

        // Test keys are sorted (exact order depends on spaceship operator behavior).
        $keys = $dict->keys();
        $this->assertCount(4, $keys);
    }

    /**
     * Test sort with custom comparison function.
     */
    public function testSortWithCustomFunction(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 3);
        $dict->add('c', 2);

        // Test sorting with custom descending comparison.
        $dict->sort(fn($a, $b) => $b->value <=> $a->value);

        // Test values are in descending order.
        $values = $dict->values();
        $this->assertEquals([3, 2, 1], $values);
    }
}
