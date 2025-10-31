<?php

declare(strict_types = 1);

namespace Galaxon\Collections\Tests\Sequence;

use Galaxon\Collections\Sequence;
use Galaxon\Collections\Dictionary;
use Galaxon\Collections\Set;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Sequence conversion methods.
 */
#[CoversClass(Sequence::class)]
class SequenceConversionTest extends TestCase
{
    /**
     * Test toString method.
     */
    public function testToString(): void
    {
        // Test: Convert Sequence to string
        $seq = new Sequence('int');
        $seq->append(1, 2, 3);
        $str = (string)$seq;
        
        // Test: Verify string representation exists
        $this->assertIsString($str);
        $this->assertNotEmpty($str);
    }

    /**
     * Test toDictionary conversion.
     */
    public function testToDictionary(): void
    {
        // Test: Convert Sequence to Dictionary
        $seq = new Sequence('string');
        $seq->append('apple', 'banana', 'cherry');
        $dict = $seq->toDictionary();
        
        // Test: Verify Dictionary created with correct mappings
        $this->assertInstanceOf(Dictionary::class, $dict);
        $this->assertCount(3, $dict);
        $this->assertSame('apple', $dict[0]);
        $this->assertSame('banana', $dict[1]);
        $this->assertSame('cherry', $dict[2]);
    }

    /**
     * Test toDictionary preserves indexes as keys.
     */
    public function testToDictionaryPreservesIndexes(): void
    {
        // Test: Verify indexes become dictionary keys
        $seq = new Sequence('int');
        $seq->append(10, 20, 30, 40, 50);
        $dict = $seq->toDictionary();
        
        // Test: Check specific key-value pairs
        $this->assertTrue($dict->offsetExists(0));
        $this->assertTrue($dict->offsetExists(4));
        $this->assertSame(10, $dict[0]);
        $this->assertSame(50, $dict[4]);
    }

    /**
     * Test toSet conversion.
     */
    public function testToSet(): void
    {
        // Test: Convert Sequence to Set
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $set = $seq->toSet();
        
        // Test: Verify Set created with correct values
        $this->assertInstanceOf(Set::class, $set);
        $this->assertCount(5, $set);
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains(5));
    }

    /**
     * Test toSet removes duplicates.
     */
    public function testToSetRemovesDuplicates(): void
    {
        // Test: Convert Sequence with duplicates to Set
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'a', 'c', 'b', 'd');
        $set = $seq->toSet();
        
        // Test: Verify Set contains only unique values
        $this->assertCount(4, $set);
        $this->assertTrue($set->contains('a'));
        $this->assertTrue($set->contains('b'));
        $this->assertTrue($set->contains('c'));
        $this->assertTrue($set->contains('d'));
    }

    /**
     * Test toArray conversion returns regular PHP array.
     */
    public function testToArrayReturnsRegularArray(): void
    {
        // Test: Convert to PHP array
        $seq = new Sequence('int');
        $seq->append(5, 10, 15, 20);
        $array = $seq->toArray();
        
        // Test: Verify it's a regular array with correct values
        $this->assertIsArray($array);
        $this->assertSame([5, 10, 15, 20], $array);
    }

    /**
     * Test toArray preserves sequential indexes.
     */
    public function testToArrayPreservesSequentialIndexes(): void
    {
        // Test: Verify array indexes are sequential
        $seq = new Sequence('string');
        $seq->append('x', 'y', 'z');
        $array = $seq->toArray();
        
        // Test: Check array structure
        $this->assertArrayHasKey(0, $array);
        $this->assertArrayHasKey(1, $array);
        $this->assertArrayHasKey(2, $array);
        $this->assertSame('x', $array[0]);
        $this->assertSame('z', $array[2]);
    }

    /**
     * Test countValues method.
     */
    public function testCountValues(): void
    {
        // Test: Count occurrences of each value
        $seq = new Sequence('string');
        $seq->append('a', 'b', 'a', 'c', 'b', 'a');
        $counts = $seq->countValues();
        
        // Test: Verify counts are correct
        $this->assertInstanceOf(Dictionary::class, $counts);
        $this->assertSame(3, $counts['a']);
        $this->assertSame(2, $counts['b']);
        $this->assertSame(1, $counts['c']);
    }

    /**
     * Test countValues with unique values.
     */
    public function testCountValuesWithUniqueValues(): void
    {
        // Test: Count when all values are unique
        $seq = new Sequence('int');
        $seq->append(1, 2, 3, 4, 5);
        $counts = $seq->countValues();
        
        // Test: Verify all counts are 1
        $this->assertCount(5, $counts);
        $this->assertSame(1, $counts[1]);
        $this->assertSame(1, $counts[5]);
    }
}
