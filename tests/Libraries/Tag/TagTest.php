<?php

namespace BNETDocs\Tests\Libraries\Tag;

use \BNETDocs\Libraries\Tag\Tag;
use \BNETDocs\Libraries\Tag\Types;
use \OutOfBoundsException;
use \PHPUnit\Framework\TestCase;
use \stdClass;
use \ValueError;

class TagTest extends TestCase
{
    private Tag $tag;

    protected function setUp(): void
    {
        // null argument → is_object(null) = false → constructor body skipped.
        // All private properties retain their initialiser values (null).
        $this->tag = new Tag(null);
    }

    // Default state after null construction

    public function testDefaultReferenceIdIsNull(): void
    {
        $this->assertNull($this->tag->getReferenceId());
    }

    public function testDefaultReferenceTypeIsNull(): void
    {
        $this->assertNull($this->tag->getReferenceType());
    }

    public function testDefaultTagStringIsNull(): void
    {
        $this->assertNull($this->tag->getTagString());
    }

    public function testDefaultCreatedDateTimeIsNull(): void
    {
        $this->assertNull($this->tag->getCreatedDateTime());
    }

    // __toString

    public function testToStringWithNullTagStringReturnsEmpty(): void
    {
        $this->assertSame('', (string) $this->tag);
    }

    public function testToStringReturnsTagString(): void
    {
        $this->tag->setTagString('battle-net');
        $this->assertSame('battle-net', (string) $this->tag);
    }

    // setTagString

    public function testSetTagStringChangesValue(): void
    {
        $this->tag->setTagString('protocol');
        $this->assertSame('protocol', $this->tag->getTagString());
    }

    public function testSetTagStringNullIsAllowed(): void
    {
        $this->tag->setTagString('anything');
        $this->tag->setTagString(null);
        $this->assertNull($this->tag->getTagString());
    }

    // setReferenceId

    public function testSetReferenceIdPositiveIsAllowed(): void
    {
        $this->tag->setReferenceId(42);
        $this->assertSame(42, $this->tag->getReferenceId());
    }

    public function testSetReferenceIdNullIsAllowed(): void
    {
        $this->tag->setReferenceId(42);
        $this->tag->setReferenceId(null);
        $this->assertNull($this->tag->getReferenceId());
    }

    public function testSetReferenceIdNegativeThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->tag->setReferenceId(-1);
    }

    public function testSetReferenceIdZeroIsAllowed(): void
    {
        // Also implicitly verifies the lower bound (0) is accepted.
        $this->tag->setReferenceId(0);
        $this->assertSame(0, $this->tag->getReferenceId());
    }

    // setReferenceType — accepts null, Types enum, or int (converts to enum)

    public function testSetReferenceTypeWithEnumIsStored(): void
    {
        $this->tag->setReferenceType(Types::Packet);
        $this->assertSame(Types::Packet, $this->tag->getReferenceType());
    }

    public function testSetReferenceTypeWithIntConvertsToEnum(): void
    {
        $this->tag->setReferenceType(Types::Document->toInt());
        $this->assertSame(Types::Document, $this->tag->getReferenceType());
    }

    public function testSetReferenceTypeNullIsAllowed(): void
    {
        $this->tag->setReferenceType(Types::Server);
        $this->tag->setReferenceType(null);
        $this->assertNull($this->tag->getReferenceType());
    }

    public function testSetReferenceTypeAllEnumCasesAreAccepted(): void
    {
        foreach (Types::cases() as $case) {
            $this->tag->setReferenceType($case);
            $this->assertSame($case, $this->tag->getReferenceType());
        }
    }

    public function testSetReferenceTypeInvalidIntThrowsValueError(): void
    {
        // Invalid int that has no matching Types case → fromInt() throws ValueError
        $this->expectException(ValueError::class);
        $this->tag->setReferenceType(99);
    }

    // setCreatedDateTime

    public function testSetCreatedDateTimeFromString(): void
    {
        $this->tag->setCreatedDateTime('2024-06-01 00:00:00');
        $this->assertNotNull($this->tag->getCreatedDateTime());
        $this->assertSame('2024-06-01', $this->tag->getCreatedDateTime()->format('Y-m-d'));
    }

    public function testSetCreatedDateTimeNullIsAllowed(): void
    {
        $this->tag->setCreatedDateTime('2024-01-01');
        $this->tag->setCreatedDateTime(null);
        $this->assertNull($this->tag->getCreatedDateTime());
    }

    // StdClass construction path

    public function testStdClassConstructionSetsAllFields(): void
    {
        $obj = new stdClass();
        $obj->reference_id   = 7;
        $obj->reference_type = Types::Packet->toInt();
        $obj->tag_string     = 'chat';
        $obj->created_datetime = '2024-01-15 10:00:00';

        $t = new Tag($obj);
        $this->assertSame(7, $t->getReferenceId());
        $this->assertSame(Types::Packet, $t->getReferenceType());
        $this->assertSame('chat', $t->getTagString());
        $this->assertNotNull($t->getCreatedDateTime());
    }

    // jsonSerialize

    public function testJsonSerializeHasExpectedKeys(): void
    {
        $data = $this->tag->jsonSerialize();
        $this->assertArrayHasKey('created_datetime', $data);
        $this->assertArrayHasKey('reference_id', $data);
        $this->assertArrayHasKey('reference_type', $data);
        $this->assertArrayHasKey('tag_string', $data);
    }

    public function testJsonSerializeDefaultAllNull(): void
    {
        $data = $this->tag->jsonSerialize();
        $this->assertNull($data['created_datetime']);
        $this->assertNull($data['reference_id']);
        $this->assertNull($data['reference_type']);
        $this->assertNull($data['tag_string']);
    }

    public function testJsonSerializeReflectsSetValues(): void
    {
        $this->tag->setReferenceId(5);
        $this->tag->setReferenceType(Types::Server);
        $this->tag->setTagString('pvpgn');

        $data = $this->tag->jsonSerialize();
        $this->assertSame(5, $data['reference_id']);
        $this->assertSame(Types::Server, $data['reference_type']);
        $this->assertSame('pvpgn', $data['tag_string']);
    }
}
