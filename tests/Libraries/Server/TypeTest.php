<?php

namespace BNETDocs\Tests\Libraries\Server;

use \BNETDocs\Libraries\Server\Type;
use \OutOfBoundsException;
use \PHPUnit\Framework\TestCase;
use \stdClass;

class TypeTest extends TestCase
{
    private Type $type;

    protected function setUp(): void
    {
        // null id → allocate() sets label='' and returns true early; no DB access.
        $this->type = new Type(null);
    }

    // Default state after null construction

    public function testDefaultIdIsNull(): void
    {
        $this->assertNull($this->type->getId());
    }

    public function testDefaultLabelIsEmpty(): void
    {
        $this->assertSame('', $this->type->getLabel());
    }

    // StdClass construction path

    public function testStdClassConstructionSetsFields(): void
    {
        $obj = new stdClass();
        $obj->id    = 3;
        $obj->label = 'Battle.net Chat';

        $t = new Type($obj);
        $this->assertSame(3, $t->getId());
        $this->assertSame('Battle.net Chat', $t->getLabel());
    }

    // setLabel validation

    public function testSetLabelEmptyStringIsAllowed(): void
    {
        $this->type->setLabel('');
        $this->assertSame('', $this->type->getLabel());
    }

    public function testSetLabelAtMaxLengthIsAllowed(): void
    {
        $value = str_repeat('x', Type::MAX_LABEL);
        $this->type->setLabel($value);
        $this->assertSame($value, $this->type->getLabel());
    }

    public function testSetLabelTooLongThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->type->setLabel(str_repeat('x', Type::MAX_LABEL + 1));
    }

    public function testSetLabelChangesValue(): void
    {
        $this->type->setLabel('BNLS');
        $this->assertSame('BNLS', $this->type->getLabel());
    }

    // setId validation

    public function testSetIdNullIsAllowed(): void
    {
        $this->type->setId(null);
        $this->assertNull($this->type->getId());
    }

    public function testSetIdPositiveIsAllowed(): void
    {
        $this->type->setId(7);
        $this->assertSame(7, $this->type->getId());
    }

    public function testSetIdNegativeThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->type->setId(-1);
    }

    // DB-free early-exit

    public function testDeallocateReturnsFalseWhenIdIsNull(): void
    {
        $this->assertFalse($this->type->deallocate());
    }

    // jsonSerialize

    public function testJsonSerializeHasExpectedKeys(): void
    {
        $data = $this->type->jsonSerialize();
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('label', $data);
    }

    public function testJsonSerializeDefaultValues(): void
    {
        $data = $this->type->jsonSerialize();
        $this->assertNull($data['id']);
        $this->assertSame('', $data['label']);
    }

    public function testJsonSerializeReflectsSetValues(): void
    {
        $this->type->setLabel('BNLS');
        $data = $this->type->jsonSerialize();
        $this->assertSame('BNLS', $data['label']);
    }
}
