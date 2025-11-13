<?php

declare(strict_types=1);

namespace Galaxon\Collections\Tests\Dictionary;

use Galaxon\Collections\Dictionary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Dictionary iteration functionality.
 */
#[CoversClass(Dictionary::class)]
class DictionaryIterationTest extends TestCase
{
    /**
     * Test iterating over dictionary with foreach.
     */
    public function testForeachIteration(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test iterating over dictionary.
        $keys = [];
        $values = [];
        foreach ($dict as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        // Test correct keys and values were iterated.
        $this->assertEquals(['a', 'b', 'c'], $keys);
        $this->assertEquals([1, 2, 3], $values);
    }

    /**
     * Test iterating over empty dictionary.
     */
    public function testForeachIterationOnEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test iterating over empty dictionary.
        $count = 0;
        foreach ($dict as $key => $value) {
            $count++;
        }

        // Test no iterations occurred.
        $this->assertEquals(0, $count);
    }

    /**
     * Test iteration preserves order.
     */
    public function testIterationPreservesOrder(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('first', 1);
        $dict->add('second', 2);
        $dict->add('third', 3);

        // Test iteration order.
        $keys = [];
        foreach ($dict as $key => $value) {
            $keys[] = $key;
        }

        // Test keys are in insertion order.
        $this->assertEquals(['first', 'second', 'third'], $keys);
    }

    /**
     * Test iteration with various key types.
     */
    public function testIterationWithVariousKeyTypes(): void
    {
        $dict = new Dictionary();
        $dict->add(1, 'int key');
        $dict->add('string', 'string key');
        $dict->add(true, 'bool key');

        // Test iterating with various key types.
        $keys = [];
        $values = [];
        foreach ($dict as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        // Test correct types were preserved.
        $this->assertIsInt($keys[0]);
        $this->assertIsString($keys[1]);
        $this->assertIsBool($keys[2]);

        // Test correct values.
        $this->assertEquals('int key', $values[0]);
        $this->assertEquals('string key', $values[1]);
        $this->assertEquals('bool key', $values[2]);
    }

    /**
     * Test modifying dictionary during iteration.
     */
    public function testModifyingDuringIteration(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test modifying values during iteration doesn't affect the iteration.
        $keys = [];
        foreach ($dict as $key => $value) {
            $keys[] = $key;
            // Modify the dictionary during iteration.
            if ($key === 'b') {
                $dict['b'] = 20;
            }
        }

        // Test iteration completed for all original items.
        $this->assertEquals(['a', 'b', 'c'], $keys);

        // Test the modification was applied.
        $this->assertEquals(20, $dict['b']);
    }

    /**
     * Test breaking out of iteration.
     */
    public function testBreakingIteration(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test breaking iteration early.
        $keys = [];
        foreach ($dict as $key => $value) {
            $keys[] = $key;
            if ($key === 'b') {
                break;
            }
        }

        // Test iteration stopped at 'b'.
        $this->assertEquals(['a', 'b'], $keys);
    }

    /**
     * Test continuing in iteration.
     */
    public function testContinuingIteration(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test skipping items with continue.
        $sum = 0;
        foreach ($dict as $key => $value) {
            /** @var int $value */
            if ($key === 'b') {
                continue;
            }
            $sum += $value;
        }

        // Test 'b' was skipped.
        $this->assertEquals(4, $sum); // 1 + 3, skipping 2
    }

    /**
     * Test nested iteration.
     */
    public function testNestedIteration(): void
    {
        $dict1 = new Dictionary('string', 'int');
        $dict1->add('a', 1);
        $dict1->add('b', 2);

        $dict2 = new Dictionary('string', 'int');
        $dict2->add('x', 10);
        $dict2->add('y', 20);

        // Test nested iteration.
        $results = [];
        foreach ($dict1 as $key1 => $value1) {
            foreach ($dict2 as $key2 => $value2) {
                /** @var string $key1 */
                /** @var string $key2 */
                $results[] = "$key1-$key2";
            }
        }

        // Test all combinations were iterated.
        $this->assertEquals(['a-x', 'a-y', 'b-x', 'b-y'], $results);
    }

    /**
     * Test getIterator returns a Traversable.
     */
    public function testGetIteratorReturnsTraversable(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        // Test getting iterator.
        $iterator = $dict->getIterator();

        // Test iterator is traversable.
        $this->assertInstanceOf(\Traversable::class, $iterator);
    }

    /**
     * Test using iterator_to_array.
     */
    public function testIteratorToArray(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test converting iterator to array.
        $array = iterator_to_array($dict);

        // Test correct array was created.
        $this->assertIsArray($array);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $array);
    }

    /**
     * Test multiple iterations over same dictionary.
     */
    public function testMultipleIterations(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test first iteration.
        $keys1 = [];
        foreach ($dict as $key => $value) {
            $keys1[] = $key;
        }

        // Test second iteration.
        $keys2 = [];
        foreach ($dict as $key => $value) {
            $keys2[] = $key;
        }

        // Test both iterations produced same results.
        $this->assertEquals($keys1, $keys2);
        $this->assertEquals(['a', 'b'], $keys1);
    }
}
