<?php

declare(strict_types=1);

namespace Mossy2100\Collections\Tests\Dictionary;

use Mossy2100\Collections\Collection;
use Mossy2100\Collections\Dictionary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Dictionary inspection methods.
 */
#[CoversClass(Dictionary::class)]
#[CoversClass(Collection::class)]
class DictionaryInspectionTest extends TestCase
{
    #region Method contains() tests.

    /**
     * Test contains returns true when value exists.
     */
    public function testContainsReturnsTrueWhenValueExists(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);

        // Test checking for existing value.
        $this->assertTrue($dict->contains(1));
        $this->assertTrue($dict->contains(2));
    }

    /**
     * Test contains returns false when value does not exist.
     */
    public function testContainsReturnsFalseWhenValueDoesNotExist(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);

        // Test checking for non-existent value.
        $this->assertFalse($dict->contains(999));
    }

    /**
     * Test contains with duplicate values.
     */
    public function testContainsWithDuplicateValues(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 1);
        $dict->add('c', 2);

        // Test contains finds value even when duplicated.
        $this->assertTrue($dict->contains(1));
    }

    /**
     * Test contains uses strict equality.
     */
    public function testContainsUsesStrictEquality(): void
    {
        $dict = new Dictionary('string', 'mixed');
        $dict->add('a', 1);
        $dict->add('b', '1');

        // Test strict equality - int 1 should not match string '1'.
        $this->assertTrue($dict->contains(1));
        $this->assertTrue($dict->contains('1'));
        $this->assertFalse($dict->contains(true));
    }

    /**
     * Test contains finds a NAN value.
     */
    public function testContainsNanValue(): void
    {
        $dict = new Dictionary('string', 'float');
        $dict['a'] = 1.0;
        $dict['b'] = NAN;

        $this->assertTrue($dict->contains(NAN));
    }

    /**
     * Test contains distinguishes a -0.0 value from 0.0.
     */
    public function testContainsDistinguishesNegativeZero(): void
    {
        $dict = new Dictionary('string', 'float');
        $dict['a'] = 0.0;

        $this->assertTrue($dict->contains(0.0));
        $this->assertFalse($dict->contains(-0.0));
    }

    #endregion

    #region Method keyExists() tests.

    /**
     * Test keyExists returns true for existing key.
     */
    public function testKeyExistsReturnsTrueForExistingKey(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('key', 123);

        // Test checking for existing key.
        $this->assertTrue($dict->keyExists('key'));
    }

    /**
     * Test keyExists returns false for non-existent key.
     */
    public function testKeyExistsReturnsFalseForNonExistentKey(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test checking for non-existent key.
        $this->assertFalse($dict->keyExists('nonexistent'));
    }

    /**
     * Test keyExists with null key.
     */
    public function testKeyExistsWithNullKey(): void
    {
        $dict = new Dictionary('?string', 'int');
        $dict->add(null, 123);

        // Test checking for null key.
        $this->assertTrue($dict->keyExists(null));
    }

    /**
     * Test keyExists with various key types.
     */
    public function testKeyExistsWithVariousKeyTypes(): void
    {
        $dict = new Dictionary();
        $dict->add(1, 'int key');
        $dict->add('string', 'string key');
        $dict->add(true, 'bool key');

        // Test checking for keys of various types.
        $this->assertTrue($dict->keyExists(1));
        $this->assertTrue($dict->keyExists('string'));
        $this->assertTrue($dict->keyExists(true));
        $this->assertFalse($dict->keyExists(2));
    }

    #endregion

    #region Method empty() tests.

    /**
     * Test empty returns true for empty dictionary.
     */
    public function testEmptyReturnsTrueForEmptyDictionary(): void
    {
        $dict = new Dictionary();

        // Test empty dictionary.
        $this->assertTrue($dict->empty());
    }

    /**
     * Test empty returns false for non-empty dictionary.
     */
    public function testEmptyReturnsFalseForNonEmptyDictionary(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('key', 123);

        // Test non-empty dictionary.
        $this->assertFalse($dict->empty());
    }

    #endregion

    #region Method all() tests.

    /**
     * Test all returns true when all items pass test.
     */
    public function testAllReturnsTrueWhenAllPass(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 2);
        $dict->add('b', 4);
        $dict->add('c', 6);

        // Test all items are even.
        $result = $dict->all(static fn ($pair) => $pair->value % 2 === 0);

        $this->assertTrue($result);
    }

    /**
     * Test all returns false when some items fail test.
     */
    public function testAllReturnsFalseWhenSomeFail(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 2);
        $dict->add('b', 3);
        $dict->add('c', 4);

        // Test not all items are even.
        $result = $dict->all(static fn ($pair) => $pair->value % 2 === 0);

        $this->assertFalse($result);
    }

    /**
     * Test all on empty dictionary returns true.
     */
    public function testAllOnEmptyDictionaryReturnsTrue(): void
    {
        $dict = new Dictionary();

        // Test all on empty dictionary.
        $result = $dict->all(static fn ($pair) => false);

        $this->assertTrue($result);
    }

    #endregion

    #region Method any() tests.

    /**
     * Test any returns true when at least one item passes test.
     */
    public function testAnyReturnsTrueWhenAtLeastOnePasses(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 2);
        $dict->add('c', 3);

        // Test at least one item is even.
        $result = $dict->any(static fn ($pair) => $pair->value % 2 === 0);

        $this->assertTrue($result);
    }

    /**
     * Test any returns false when no items pass test.
     */
    public function testAnyReturnsFalseWhenNonePass(): void
    {
        $dict = new Dictionary('string', 'int');
        $dict->add('a', 1);
        $dict->add('b', 3);
        $dict->add('c', 5);

        // Test no items are even.
        $result = $dict->any(static fn ($pair) => $pair->value % 2 === 0);

        $this->assertFalse($result);
    }

    /**
     * Test any on empty dictionary returns false.
     */
    public function testAnyOnEmptyDictionaryReturnsFalse(): void
    {
        $dict = new Dictionary();

        // Test any on empty dictionary.
        $result = $dict->any(static fn ($pair) => true);

        $this->assertFalse($result);
    }

    #endregion

    #region Method count() tests.

    /**
     * Test count interface works correctly.
     */
    public function testCountInterface(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test count on empty dictionary.
        $this->assertCount(0, $dict);

        // Test count after adding items.
        $dict->add('a', 1);
        $this->assertCount(1, $dict);

        $dict->add('b', 2);
        $this->assertCount(2, $dict);

        // Test count after removing items.
        $dict->removeByKey('a');
        $this->assertCount(1, $dict);
    }

    /**
     * Test count returns correct number of items.
     */
    public function testCountReturnsCorrectNumber(): void
    {
        $dict = new Dictionary('string', 'int');

        // Test count on empty dictionary.
        $this->assertCount(0, $dict);

        // Test count after adding items.
        $dict->add('a', 1);
        $this->assertCount(1, $dict);

        $dict->add('b', 2);
        $this->assertCount(2, $dict);

        $dict->add('c', 3);
        $this->assertCount(3, $dict);
    }

    #endregion
}
