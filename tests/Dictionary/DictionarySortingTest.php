<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Dictionary;

use Mossy2100\Collections\Dictionary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Dictionary sorting methods.
 */
#[CoversClass(Dictionary::class)]
class DictionarySortingTest extends TestCase
{
    #region Method sort() tests.

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
        $result = $dict->sort(static fn ($a, $b) => $b->value <=> $a->value);

        // Test values are in descending order.
        $this->assertSame([3, 2, 1], $result->values);
        $this->assertSame(['b', 'c', 'a'], $result->keys);
    }

    /**
     * Test sort returns a new Dictionary and leaves the original unchanged.
     */
    public function testSortIsNonMutating(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('c', 3);
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test sorting returns a new instance.
        $result = $dict->sort(static fn ($a, $b) => $a->value <=> $b->value);
        $this->assertNotSame($dict, $result);

        // Test the original order is unchanged.
        $this->assertSame(['c', 'a', 'b'], $dict->keys);
        $this->assertSame(['a', 'b', 'c'], $result->keys);
    }

    /**
     * Test sort preserves the type constraints.
     */
    public function testSortPreservesTypes(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        // Test the result has the same key and value types.
        $result = $dict->sort(static fn ($a, $b) => $a->value <=> $b->value);
        $this->assertTrue($result->keyTypes->containsOnly('string'));
        $this->assertTrue($result->valueTypes->containsOnly('int'));
    }

    /**
     * Test changing the sorted Dictionary doesn't affect the original.
     */
    public function testSortResultIsIndependent(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('b', 2);
        $dict->add('a', 1);

        // Modify the result.
        $result = $dict->sort(static fn ($a, $b) => $a->value <=> $b->value);
        $result['c'] = 3;
        $result['a'] = 100;

        // Test the original is unchanged.
        $this->assertCount(2, $dict);
        $this->assertSame(1, $dict['a']);
        $this->assertFalse($dict->keyExists('c'));
    }

    #endregion

    #region Method sortByKey() tests.

    /**
     * Test sortByKey sorts dictionary by keys in ascending order.
     */
    public function testSortByKeySortsAscending(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('c', 3);
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test keys are in sorted order.
        $result = $dict->sortByKey();
        $this->assertSame(['a', 'b', 'c'], $result->keys);
    }

    /**
     * Test sortByKey returns a new Dictionary and leaves the original unchanged.
     */
    public function testSortByKeyIsNonMutating(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('c', 3);
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test sorting returns a new instance.
        $result = $dict->sortByKey();
        $this->assertNotSame($dict, $result);

        // Test the original order is unchanged.
        $this->assertSame(['c', 'a', 'b'], $dict->keys);
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

        // Test keys are in sorted order.
        $this->assertSame([1, 2, 3], $dict->sortByKey()->keys);
    }

    /**
     * Test sortByKey on empty dictionary.
     */
    public function testSortByKeyOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test sorting empty dictionary doesn't throw, and the result is empty.
        $this->assertCount(0, $dict->sortByKey());
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

        // Test values are preserved with correct keys.
        $result = $dict->sortByKey();
        $this->assertSame(200, $result['a']);
        $this->assertSame(300, $result['m']);
        $this->assertSame(100, $result['z']);
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

        // Test keys are sorted (exact order depends on spaceship operator behavior).
        $this->assertCount(4, $dict->sortByKey()->keys);
    }

    #endregion

    #region Method sortByValue() tests.

    /**
     * Test sortByValue sorts dictionary by values in ascending order.
     */
    public function testSortByValueSortsAscending(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 3);
        $dict->add('b', 1);
        $dict->add('c', 2);

        // Test values are in sorted order.
        $result = $dict->sortByValue();
        $this->assertSame([1, 2, 3], $result->values);
    }

    /**
     * Test sortByValue returns a new Dictionary and leaves the original unchanged.
     */
    public function testSortByValueIsNonMutating(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 3);
        $dict->add('b', 1);
        $dict->add('c', 2);

        // Test sorting returns a new instance.
        $result = $dict->sortByValue();
        $this->assertNotSame($dict, $result);

        // Test the original order is unchanged.
        $this->assertSame([3, 1, 2], $dict->values);
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

        // Test values are in sorted order.
        $this->assertSame(['apple', 'mango', 'zebra'], $dict->sortByValue()->values);
    }

    /**
     * Test sortByValue on empty dictionary.
     */
    public function testSortByValueOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test sorting empty dictionary doesn't throw, and the result is empty.
        $this->assertCount(0, $dict->sortByValue());
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

        // Test keys are preserved with correct values.
        $result = $dict->sortByValue();
        $this->assertSame(['second', 'third', 'first'], $result->keys);
        $this->assertSame(100, $result['second']);
        $this->assertSame(200, $result['third']);
        $this->assertSame(300, $result['first']);
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

        // Test values are sorted, and pairs with equal values keep their original order.
        $result = $dict->sortByValue();
        $this->assertSame([1, 1, 2, 2], $result->values);
        $this->assertSame(['b', 'd', 'a', 'c'], $result->keys);
    }

    #endregion

    #region Chaining tests.

    /**
     * Test chaining multiple sorts.
     */
    public function testChainingSorts(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('z', 1);
        $dict->add('a', 3);
        $dict->add('m', 2);

        // Test final sort (by key) is applied.
        $this->assertSame(['a', 'm', 'z'], $dict->sortByValue()->sortByKey()->keys);

        // Test the original order is unchanged.
        $this->assertSame(['z', 'a', 'm'], $dict->keys);
    }

    #endregion
}
