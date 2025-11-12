<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Dictionary;

use Galaxon\Collections\Dictionary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;
use ValueError;

/**
 * Tests for Dictionary transformation methods (flip, merge, filter).
 */
#[CoversClass(Dictionary::class)]
class DictionaryTransformTest extends TestCase
{
    /**
     * Test flip swaps keys and values.
     */
    public function testFlipSwapsKeysAndValues(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test flipping the dictionary.
        $flipped = $dict->flip();

        // Check key typeset is as expected.
        $this->assertTrue($flipped->keyTypes->containsOnly('int'));

        // Check value typeset is as expected.
        $this->assertTrue($flipped->valueTypes->containsOnly('string'));

        // Test keys and values are swapped.
        $this->assertCount(3, $flipped);
        $this->assertEquals('a', $flipped[1]);
        $this->assertEquals('b', $flipped[2]);
        $this->assertEquals('c', $flipped[3]);

        // Test original dictionary is unchanged.
        $this->assertCount(3, $dict);
        $this->assertEquals(1, $dict['a']);
    }

    /**
     * Test flip on empty dictionary.
     */
    public function testFlipOnEmptyDictionary(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test flipping empty dictionary.
        $flipped = $dict->flip();

        // Test flipped dictionary is also empty.
        $this->assertCount(0, $flipped);
        $this->assertTrue($flipped->empty());
    }

    /**
     * Test flip with duplicate values throws ValueError.
     */
    public function testFlipWithDuplicateValuesThrowsValueError(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('first', 1);
        $dict->add('second', 2);
        $dict->add('third', 1);

        // Test flipping with duplicate values throws ValueError.
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("Cannot flip Dictionary: values are not unique.");
        $dict->flip();
    }

    /**
     * Test merge combines two dictionaries.
     */
    public function testMergeCombinesDictionaries(): void
    {
        $dict1 = new Dictionary('string', 'int');
        $dict1->add('a', 1);
        $dict1->add('b', 2);

        $dict2 = new Dictionary('string', 'int');
        $dict2->add('c', 3);
        $dict2->add('d', 4);

        // Test merging dictionaries.
        $merged = $dict1->merge($dict2);

        // Test all items from both dictionaries are present.
        $this->assertCount(4, $merged);
        $this->assertEquals(1, $merged['a']);
        $this->assertEquals(2, $merged['b']);
        $this->assertEquals(3, $merged['c']);
        $this->assertEquals(4, $merged['d']);

        // Test original dictionaries are unchanged.
        $this->assertCount(2, $dict1);
        $this->assertCount(2, $dict2);
    }

    /**
     * Test merge with overlapping keys keeps value from second dictionary.
     */
    public function testMergeWithOverlappingKeys(): void
    {
        $dict1 = new Dictionary('string', 'int');
        $dict1->add('a', 1);
        $dict1->add('b', 2);

        $dict2 = new Dictionary('string', 'int');
        $dict2->add('b', 20);
        $dict2->add('c', 3);

        // Test merging with overlapping keys.
        $merged = $dict1->merge($dict2);

        // Test value from second dictionary is kept.
        $this->assertCount(3, $merged);
        $this->assertEquals(1, $merged['a']);
        $this->assertEquals(20, $merged['b']); // Value from dict2
        $this->assertEquals(3, $merged['c']);
    }

    /**
     * Test merge with empty dictionary.
     */
    public function testMergeWithEmptyDictionary(): void
    {
        $dict1 = new Dictionary('string', 'int');
        $dict1->add('a', 1);

        $dict2 = new Dictionary('string', 'int');

        // Test merging with empty dictionary.
        $merged = $dict1->merge($dict2);

        // Test result contains only items from first dictionary.
        $this->assertCount(1, $merged);
        $this->assertEquals(1, $merged['a']);
    }

    /**
     * Test merge combines type constraints.
     */
    public function testMergeCombinesTypeConstraints(): void
    {
        $dict1 = new Dictionary('string', 'int');
        $dict1->add('a', 1);

        $dict2 = new Dictionary('string', 'float');
        $dict2->add('b', 2.5);

        // Test merging dictionaries with different value types.
        $merged = $dict1->merge($dict2);

        // Confirm value typeset in merged dictionary contains two types.
        $this->assertCount(2, $merged->valueTypes);
        $this->assertTrue($merged->valueTypes->containsAll('int', 'float'));

        // Test both value types are allowed in merged dictionary.
        $this->assertCount(2, $merged);
        $this->assertEquals(1, $merged['a']);
        $this->assertEquals(2.5, $merged['b']);
    }

    /**
     * Test filter keeps items that pass test.
     */
    public function testFilterKeepsMatchingItems(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);
        $dict->add('d', 4);

        // Test filtering for even values.
        $filtered = $dict->filter(fn($key, $value) => $value % 2 === 0);

        // Test only even values are kept.
        $this->assertCount(2, $filtered);
        $this->assertEquals(2, $filtered['b']);
        $this->assertEquals(4, $filtered['d']);

        // Test original dictionary is unchanged.
        $this->assertCount(4, $dict);
    }

    /**
     * Test filter can use both key and value.
     */
    public function testFilterWithKeyAndValue(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('apple', 5);
        $dict->add('banana', 3);
        $dict->add('avocado', 7);

        // Test filtering based on key starting with 'a'.
        $filtered = $dict->filter(fn($key, $value) => str_starts_with($key, 'a'));

        // Test only items with keys starting with 'a' are kept.
        $this->assertCount(2, $filtered);
        $this->assertEquals(5, $filtered['apple']);
        $this->assertEquals(7, $filtered['avocado']);
    }

    /**
     * Test filter returns empty dictionary when no items match.
     */
    public function testFilterReturnsEmptyWhenNoMatches(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test filtering with no matches.
        $filtered = $dict->filter(fn($key, $value) => $value > 10);

        // Test result is empty.
        $this->assertCount(0, $filtered);
        $this->assertTrue($filtered->empty());
    }

    /**
     * Test filter on empty dictionary.
     */
    public function testFilterOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test filtering empty dictionary.
        $filtered = $dict->filter(fn($key, $value) => true);

        // Test result is empty.
        $this->assertCount(0, $filtered);
    }

    /**
     * Test filter preserves type constraints.
     */
    public function testFilterPreservesTypeConstraints(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test filtering creates dictionary with same types.
        $filtered = $dict->filter(fn($key, $value) => $value > 1);

        // Test type constraints are preserved - we can add items with same types.
        $filtered->add('d', 4);
        $this->assertCount(3, $filtered);
    }

    /**
     * Test filter callback must return bool.
     */
    public function testFilterCallbackMustReturnBool(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        // Test callback returning non-bool throws TypeError.
        $this->expectException(TypeError::class);
        $dict->filter(fn($key, $value) => $value); // Returns int, not bool
    }

    /**
     * Test filter preserves order.
     */
    public function testFilterPreservesOrder(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('first', 1);
        $dict->add('second', 2);
        $dict->add('third', 3);
        $dict->add('fourth', 4);

        // Test filtering preserves order.
        $filtered = $dict->filter(fn($key, $value) => $value % 2 === 0);

        // Test order is preserved.
        $keys = $filtered->keys();
        $this->assertEquals(['second', 'fourth'], $keys);
    }

    /**
     * Test filter keeps all items when callback always returns true.
     */
    public function testFilterKeepsAllWithAlwaysTrue(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test filtering with always-true callback.
        $filtered = $dict->filter(fn($key, $value) => true);

        // Test all items are kept.
        $this->assertCount(3, $filtered);
        $this->assertEquals(1, $filtered['a']);
        $this->assertEquals(2, $filtered['b']);
        $this->assertEquals(3, $filtered['c']);
    }
}
