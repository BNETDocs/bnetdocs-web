<?php

namespace BNETDocs\Tests\Libraries\Core;

use \BNETDocs\Libraries\Core\ArrayFlattener;
use \DateTime;
use \DateTimeInterface;
use \Error;
use \PHPUnit\Framework\TestCase;

class ArrayFlattenerTest extends TestCase
{
    // Constructor

    public function testConstructorThrowsError(): void
    {
        // Constructor is private; PHP enforces visibility before the body runs,
        // so an Error is thrown rather than the LogicException in the body.
        $this->expectException(Error::class);
        new ArrayFlattener();
    }

    // flatten — scalar types

    public function testFlattenStringValue(): void
    {
        $data = ['key' => 'hello'];
        $result = ArrayFlattener::flatten($data);
        $this->assertSame('key hello' . PHP_EOL, $result);
    }

    public function testFlattenIntegerValue(): void
    {
        $data = ['count' => 42];
        $result = ArrayFlattener::flatten($data);
        $this->assertSame('count 42' . PHP_EOL, $result);
    }

    public function testFlattenFloatValue(): void
    {
        $data = ['ratio' => 3.14];
        $result = ArrayFlattener::flatten($data);
        $this->assertSame('ratio 3.14' . PHP_EOL, $result);
    }

    // flatten — null and bool

    public function testFlattenNullValue(): void
    {
        $data = ['field' => null];
        $result = ArrayFlattener::flatten($data);
        $this->assertSame('field null' . PHP_EOL, $result);
    }

    public function testFlattenBoolTrue(): void
    {
        $data = ['enabled' => true];
        $result = ArrayFlattener::flatten($data);
        $this->assertSame('enabled true' . PHP_EOL, $result);
    }

    public function testFlattenBoolFalse(): void
    {
        $data = ['enabled' => false];
        $result = ArrayFlattener::flatten($data);
        $this->assertSame('enabled false' . PHP_EOL, $result);
    }

    // flatten — empty array value

    public function testFlattenEmptyArrayValue(): void
    {
        $data = ['items' => []];
        $result = ArrayFlattener::flatten($data);
        $this->assertSame('items' . PHP_EOL, $result);
    }

    // flatten — nested array

    public function testFlattenNestedArray(): void
    {
        $data = ['parent' => ['child' => 'value']];
        $result = ArrayFlattener::flatten($data);
        $this->assertSame('parent_child value' . PHP_EOL, $result);
    }

    // flatten — multiple top-level keys

    public function testFlattenMultipleKeys(): void
    {
        $data = ['a' => 1, 'b' => 2];
        $result = ArrayFlattener::flatten($data);
        $this->assertSame('a 1' . PHP_EOL . 'b 2' . PHP_EOL, $result);
    }

    // flatten — empty top-level array

    public function testFlattenEmptyArray(): void
    {
        $data = [];
        $result = ArrayFlattener::flatten($data);
        $this->assertSame('', $result);
    }

    // flatten — DateTimeInterface value

    public function testFlattenDateTimeProducesThreeLines(): void
    {
        $dt = new DateTime('2024-01-15 12:00:00', new \DateTimeZone('UTC'));
        $data = ['ts' => $dt];
        $result = ArrayFlattener::flatten($data);

        $expectedIso  = 'ts_iso '  . $dt->format(DateTimeInterface::RFC2822) . PHP_EOL;
        $expectedTz   = 'ts_tz '   . $dt->format('e')                        . PHP_EOL;
        $expectedUnix = 'ts_unix '  . $dt->format('U')                        . PHP_EOL;

        $this->assertSame($expectedIso . $expectedTz . $expectedUnix, $result);
    }

    // flatten — object

    public function testFlattenObject(): void
    {
        $obj = new \stdClass();
        $obj->name = 'test';
        $result = ArrayFlattener::flatten($obj);
        $this->assertSame('name test' . PHP_EOL, $result);
    }
}
