<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Dictionary;

use Mossy2100\Collections\Dictionary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Dictionary comparison methods.
 */
#[CoversClass(Dictionary::class)]
class DictionaryComparisonTest extends TestCase
{
    #region Method equal() tests.

    /**
     * Test equals returns true for identical dictionaries.
     */
    public function testEqReturnsTrueForIdenticalDictionaries(): void
    {
        $dict1 = new Dictionary('string', 'int');
        $dict1->add('a', 1);
        $dict1->add('b', 2);

        $dict2 = new Dictionary('string', 'int');
        $dict2->add('a', 1);
        $dict2->add('b', 2);

        // Test dictionaries are equal.
        $this->assertTrue($dict1->equal($dict2));
        $this->assertTrue($dict2->equal($dict1));
    }

    /**
     * Test equals returns false for dictionaries with different values.
     */
    public function testEqReturnsFalseForDifferentValues(): void
    {
        $dict1 = new Dictionary('string', 'int');
        $dict1->add('a', 1);

        $dict2 = new Dictionary('string', 'int');
        $dict2->add('a', 2);

        // Test dictionaries are not equal.
        $this->assertFalse($dict1->equal($dict2));
    }

    /**
     * Test equals returns false for dictionaries with different keys.
     */
    public function testEqReturnsFalseForDifferentKeys(): void
    {
        $dict1 = new Dictionary('string', 'int');
        $dict1->add('a', 1);

        $dict2 = new Dictionary('string', 'int');
        $dict2->add('b', 1);

        // Test dictionaries are not equal.
        $this->assertFalse($dict1->equal($dict2));
    }

    /**
     * Test equals returns false for dictionaries with different counts.
     */
    public function testEqReturnsFalseForDifferentCounts(): void
    {
        $dict1 = new Dictionary('string', 'int');
        $dict1->add('a', 1);

        $dict2 = new Dictionary('string', 'int');
        $dict2->add('a', 1);
        $dict2->add('b', 2);

        // Test dictionaries are not equal.
        $this->assertFalse($dict1->equal($dict2));
    }

    /**
     * Test equals returns false for different dictionary order.
     */
    public function testEqReturnsFalseForDifferentOrder(): void
    {
        $dict1 = new Dictionary('string', 'int');
        $dict1->add('a', 1);
        $dict1->add('b', 2);

        $dict2 = new Dictionary('string', 'int');
        $dict2->add('b', 2);
        $dict2->add('a', 1);

        // Test dictionaries are not equal due to order.
        $this->assertFalse($dict1->equal($dict2));
    }

    /**
     * Test equals on empty dictionaries.
     */
    public function testEqOnEmptyDictionaries(): void
    {
        $dict1 = new Dictionary();
        $dict2 = new Dictionary();

        // Test empty dictionaries are equal.
        $this->assertTrue($dict1->equal($dict2));
    }

    /**
     * Test Dictionaries with NAN values compare as equal.
     */
    public function testEqualWithNanValues(): void
    {
        $dict1 = new Dictionary('string', 'float|array');
        $dict1['a'] = NAN;
        $dict1['b'] = [NAN];
        $dict2 = new Dictionary('string', 'float|array');
        $dict2['a'] = NAN;
        $dict2['b'] = [NAN];

        $this->assertTrue($dict1->equal($dict2));
    }

    /**
     * Test -0.0 and 0.0 values make Dictionaries unequal.
     */
    public function testEqualDistinguishesNegativeZeroValues(): void
    {
        $dict1 = new Dictionary('string', 'float');
        $dict1['a'] = 0.0;
        $dict2 = new Dictionary('string', 'float');
        $dict2['a'] = -0.0;

        $this->assertFalse($dict1->equal($dict2));
    }

    #endregion
}
