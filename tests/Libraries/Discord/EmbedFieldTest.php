<?php

namespace BNETDocs\Tests\Libraries\Discord;

use \BNETDocs\Libraries\Discord\EmbedField;
use \LengthException;
use \PHPUnit\Framework\TestCase;

class EmbedFieldTest extends TestCase
{
    // Constructor

    public function testConstructorSetsProperties(): void
    {
        $f = new EmbedField('Name', 'Value', true);
        $data = $f->jsonSerialize();
        $this->assertSame('Name', $data['name']);
        $this->assertSame('Value', $data['value']);
        $this->assertTrue($data['inline']);
    }

    // setName

    public function testSetNameTooLongThrowsLengthException(): void
    {
        $this->expectException(LengthException::class);
        new EmbedField(str_repeat('x', EmbedField::MAX_NAME + 1), 'value', true);
    }

    public function testSetNameAtMaxLengthIsAllowed(): void
    {
        $f = new EmbedField(str_repeat('x', EmbedField::MAX_NAME), 'value', true);
        $data = $f->jsonSerialize();
        $this->assertSame(EmbedField::MAX_NAME, strlen($data['name']));
    }

    // setValue

    public function testSetValueTooLongThrowsLengthException(): void
    {
        $this->expectException(LengthException::class);
        new EmbedField('Name', str_repeat('x', EmbedField::MAX_VALUE + 1), true);
    }

    public function testSetValueTooShortThrowsLengthException(): void
    {
        // Empty string is shorter than MIN_VALUE (1)
        $this->expectException(LengthException::class);
        new EmbedField('Name', '', true);
    }

    public function testSetValueAtMaxLengthIsAllowed(): void
    {
        $f = new EmbedField('Name', str_repeat('x', EmbedField::MAX_VALUE), true);
        $data = $f->jsonSerialize();
        $this->assertSame(EmbedField::MAX_VALUE, strlen((string) $data['value']));
    }

    public function testSetValueWithInteger(): void
    {
        // The property is typed `string`, so int is coerced on assignment.
        $f = new EmbedField('Count', 42, true);
        $data = $f->jsonSerialize();
        $this->assertSame('42', $data['value']);
    }

    public function testSetValueWithFloat(): void
    {
        // The property is typed `string`, so float is coerced on assignment.
        $f = new EmbedField('Ratio', 3.14, true);
        $data = $f->jsonSerialize();
        $this->assertSame('3.14', $data['value']);
    }

    // jsonSerialize — inline=false is stripped by empty()

    public function testJsonSerializeInlineFalseIsStrippedByEmpty(): void
    {
        // empty(false) === true, so inline=false is removed from the serialized output.
        // This is a known quirk of the implementation.
        $f = new EmbedField('Name', 'Value', false);
        $data = $f->jsonSerialize();
        $this->assertArrayNotHasKey('inline', $data);
    }

    public function testJsonSerializeInlineTrueIsPresent(): void
    {
        $f = new EmbedField('Name', 'Value', true);
        $data = $f->jsonSerialize();
        $this->assertArrayHasKey('inline', $data);
        $this->assertTrue($data['inline']);
    }

    // Constants

    public function testMaxNameConstant(): void
    {
        $this->assertSame(256, EmbedField::MAX_NAME);
    }

    public function testMaxValueConstant(): void
    {
        $this->assertSame(1024, EmbedField::MAX_VALUE);
    }

    public function testMinValueConstant(): void
    {
        $this->assertSame(1, EmbedField::MIN_VALUE);
    }
}
