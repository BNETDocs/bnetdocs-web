<?php

namespace BNETDocs\Tests\Libraries\Core;

use \BNETDocs\Libraries\Core\ArrayTypeCheck;
use \PHPUnit\Framework\TestCase;

class ArrayTypeCheckTest extends TestCase
{
    // values()

    public function testValuesAllStrings(): void
    {
        $this->assertTrue(ArrayTypeCheck::values(['a', 'b', 'c'], 'string'));
    }

    public function testValuesAllIntegers(): void
    {
        $this->assertTrue(ArrayTypeCheck::values([1, 2, 3], 'integer'));
    }

    public function testValuesMixedTypesReturnsFalse(): void
    {
        $this->assertFalse(ArrayTypeCheck::values([1, 'two', 3], 'integer'));
    }

    public function testValuesEmptyArrayReturnsTrue(): void
    {
        $this->assertTrue(ArrayTypeCheck::values([], 'string'));
    }

    public function testValuesAllBooleans(): void
    {
        $this->assertTrue(ArrayTypeCheck::values([true, false, true], 'boolean'));
    }

    public function testValuesAllDoubles(): void
    {
        $this->assertTrue(ArrayTypeCheck::values([1.1, 2.2], 'double'));
    }

    public function testValuesObjectType(): void
    {
        $obj1 = new \stdClass();
        $obj2 = new \stdClass();
        $this->assertTrue(ArrayTypeCheck::values([$obj1, $obj2], 'stdClass'));
    }

    public function testValuesObjectTypeMismatch(): void
    {
        $obj = new \stdClass();
        $this->assertFalse(ArrayTypeCheck::values([$obj], 'SomeOtherClass'));
    }

    // keys()

    public function testKeysStringKeys(): void
    {
        $this->assertTrue(ArrayTypeCheck::keys(['foo' => 1, 'bar' => 2], 'string'));
    }

    public function testKeysIntegerKeys(): void
    {
        // PHP sequential arrays use integer keys
        $this->assertTrue(ArrayTypeCheck::keys([0 => 'a', 1 => 'b'], 'integer'));
    }

    public function testKeysStringKeysWhenExpectingInteger(): void
    {
        $this->assertFalse(ArrayTypeCheck::keys(['foo' => 1], 'integer'));
    }

    public function testKeysIntegerKeysWhenExpectingString(): void
    {
        $this->assertFalse(ArrayTypeCheck::keys([0 => 'a', 1 => 'b'], 'string'));
    }

    public function testKeysEmptyArrayReturnsTrue(): void
    {
        $this->assertTrue(ArrayTypeCheck::keys([], 'string'));
    }

    // keysAndValues()
    // Returns false only when NEITHER the key NOR the value matches the type.

    public function testKeysAndValuesBothStringMatch(): void
    {
        $this->assertTrue(ArrayTypeCheck::keysAndValues(['hello' => 'world'], 'string'));
    }

    public function testKeysAndValuesEmptyArrayReturnsTrue(): void
    {
        $this->assertTrue(ArrayTypeCheck::keysAndValues([], 'string'));
    }

    public function testKeysAndValuesKeyMatchesSuffices(): void
    {
        // String key with integer value: key passes, so the entry is not rejected.
        $this->assertTrue(ArrayTypeCheck::keysAndValues(['foo' => 42], 'string'));
    }

    public function testKeysAndValuesValueMatchesSuffices(): void
    {
        // Integer key with string value: value passes, so the entry is not rejected.
        $this->assertTrue(ArrayTypeCheck::keysAndValues([0 => 'bar'], 'string'));
    }

    public function testKeysAndValuesBothMismatchReturnsFalse(): void
    {
        // Integer key with integer value when expecting 'string': both fail → false.
        $this->assertFalse(ArrayTypeCheck::keysAndValues([0 => 1], 'string'));
    }
}
