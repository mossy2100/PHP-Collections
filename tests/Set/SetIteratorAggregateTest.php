<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Set;

use Mossy2100\Collections\Set;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Set iteration.
 */
#[CoversClass(Set::class)]
class SetIteratorAggregateTest extends TestCase
{
    #region Method getIterator() tests.

    /**
     * Test iterator_to_array creates array with sequential keys.
     */
    public function testIteratorToArrayCreatesSequentialArray(): void
    {
        $set = new Set('string');
        $set->add('a', 'b', 'c');

        $array = iterator_to_array($set);

        $this->assertIsArray($array);
        $this->assertCount(3, $array);
        $this->assertArrayHasKey(0, $array);
        $this->assertArrayHasKey(1, $array);
        $this->assertArrayHasKey(2, $array);
    }

    /**
     * Test foreach iteration.
     */
    public function testForeachIteration(): void
    {
        $set = new Set('int');
        $set->add(1, 2, 3);

        $values = [];
        foreach ($set as $value) {
            $values[] = $value;
        }

        $this->assertCount(3, $values);
        $this->assertContains(1, $values);
        $this->assertContains(2, $values);
        $this->assertContains(3, $values);
    }

    /**
     * Test foreach iteration with keys.
     */
    public function testForeachIterationWithKeys(): void
    {
        $set = new Set('string');
        $set->add('a', 'b', 'c');

        $keys = [];
        $values = [];
        foreach ($set as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        // Test sequential numeric keys are generated.
        $this->assertEquals([0, 1, 2], $keys);
        $this->assertCount(3, $values);
    }

    #endregion
}
