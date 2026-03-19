<?php

namespace BNETDocs\Tests\Libraries\Packet;

use \BNETDocs\Libraries\Packet\Packet;
use \InvalidArgumentException;
use \OutOfBoundsException;
use \PHPUnit\Framework\TestCase;
use \UnexpectedValueException;

class PacketTest extends TestCase
{
    private Packet $packet;

    protected function setUp(): void
    {
        // null id → allocate() returns true early; no database access.
        $this->packet = new Packet(null);
    }

    // Default state

    public function testDefaultIdIsNull(): void
    {
        $this->assertNull($this->packet->getId());
    }

    public function testDefaultApplicationLayerId(): void
    {
        $this->assertSame(Packet::DEFAULT_APPLICATION_LAYER_ID, $this->packet->getApplicationLayerId());
    }

    public function testDefaultTransportLayerId(): void
    {
        $this->assertSame(Packet::DEFAULT_TRANSPORT_LAYER_ID, $this->packet->getTransportLayerId());
    }

    public function testDefaultDirectionIsClientServer(): void
    {
        $this->assertSame(Packet::DIRECTION_CLIENT_SERVER, $this->packet->getDirection());
    }

    public function testDefaultEditedCountIsZero(): void
    {
        $this->assertSame(0, $this->packet->getEditedCount());
    }

    public function testDefaultEditedDateTimeIsNull(): void
    {
        $this->assertNull($this->packet->getEditedDateTime());
    }

    public function testDefaultOptionsIsMarkdown(): void
    {
        $this->assertSame(Packet::OPTION_MARKDOWN, $this->packet->getOptions());
    }

    public function testDefaultPacketIdIsZero(): void
    {
        $this->assertSame(0, $this->packet->getPacketId(false));
    }

    public function testDefaultUserIdIsNull(): void
    {
        $this->assertNull($this->packet->getUserId());
    }

    public function testDefaultUsedByIsEmpty(): void
    {
        $this->assertSame([], $this->packet->getUsedBy());
    }

    public function testGetUriIsNullWhenIdIsNull(): void
    {
        $this->assertNull($this->packet->getURI());
    }

    public function testGetTagsIsEmptyWhenIdIsNull(): void
    {
        $this->assertSame([], $this->packet->getTags());
    }

    // isXxx helpers mirror the options bitmask

    public function testIsMarkdownDefaultTrue(): void
    {
        $this->assertTrue($this->packet->isMarkdown());
    }

    public function testIsPublishedDefaultFalse(): void
    {
        $this->assertFalse($this->packet->isPublished());
    }

    public function testIsDeprecatedDefaultFalse(): void
    {
        $this->assertFalse($this->packet->isDeprecated());
    }

    public function testIsInResearchDefaultFalse(): void
    {
        $this->assertFalse($this->packet->isInResearch());
    }

    // setPublished / setDeprecated / setMarkdown / setInResearch

    public function testSetPublishedTrue(): void
    {
        $this->packet->setPublished(true);
        $this->assertTrue($this->packet->isPublished());
    }

    public function testSetPublishedFalse(): void
    {
        $this->packet->setPublished(true);
        $this->packet->setPublished(false);
        $this->assertFalse($this->packet->isPublished());
    }

    public function testSetDeprecatedTrue(): void
    {
        $this->packet->setDeprecated(true);
        $this->assertTrue($this->packet->isDeprecated());
    }

    public function testSetMarkdownFalse(): void
    {
        $this->packet->setMarkdown(false);
        $this->assertFalse($this->packet->isMarkdown());
    }

    public function testSetInResearchTrue(): void
    {
        $this->packet->setInResearch(true);
        $this->assertTrue($this->packet->isInResearch());
    }

    public function testMultipleOptionsCoexist(): void
    {
        $this->packet->setPublished(true);
        $this->packet->setDeprecated(true);
        $this->assertTrue($this->packet->isPublished());
        $this->assertTrue($this->packet->isDeprecated());
        $this->assertTrue($this->packet->isMarkdown()); // default still set
    }

    // getOption / setOption directly

    public function testGetOptionReturnsFalseForUnsetBit(): void
    {
        $this->assertFalse($this->packet->getOption(Packet::OPTION_PUBLISHED));
    }

    public function testGetOptionReturnsTrueAfterSetOption(): void
    {
        $this->packet->setOption(Packet::OPTION_PUBLISHED, true);
        $this->assertTrue($this->packet->getOption(Packet::OPTION_PUBLISHED));
    }

    // Direction — label and tag

    public function testDirectionLabelClientServer(): void
    {
        $this->assertSame('Client to Server', $this->packet->getDirectionLabel());
    }

    public function testDirectionTagClientServer(): void
    {
        $this->assertSame('C>S', $this->packet->getDirectionTag());
    }

    public function testDirectionLabelServerClient(): void
    {
        $this->packet->setDirection(Packet::DIRECTION_SERVER_CLIENT);
        $this->assertSame('Server to Client', $this->packet->getDirectionLabel());
    }

    public function testDirectionTagServerClient(): void
    {
        $this->packet->setDirection(Packet::DIRECTION_SERVER_CLIENT);
        $this->assertSame('S>C', $this->packet->getDirectionTag());
    }

    public function testDirectionLabelPeerToPeer(): void
    {
        $this->packet->setDirection(Packet::DIRECTION_PEER_TO_PEER);
        $this->assertSame('Peer to Peer', $this->packet->getDirectionLabel());
    }

    public function testDirectionTagPeerToPeer(): void
    {
        $this->packet->setDirection(Packet::DIRECTION_PEER_TO_PEER);
        $this->assertSame('P2P', $this->packet->getDirectionTag());
    }

    public function testSetDirectionInvalidThrowsUnexpectedValue(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->packet->setDirection(99);
    }

    // getPacketId formatting

    public function testGetPacketIdRawFormat(): void
    {
        $this->packet->setPacketId(0xFF);
        $this->assertSame(0xFF, $this->packet->getPacketId(false));
    }

    public function testGetPacketIdHexFormat(): void
    {
        $this->packet->setPacketId(0xFF);
        $this->assertSame('0xFF', $this->packet->getPacketId(true));
    }

    public function testGetPacketIdHexFormatZero(): void
    {
        $this->packet->setPacketId(0);
        $this->assertSame('0x00', $this->packet->getPacketId(true));
    }

    // setPacketId — multi-radix parsing

    public function testSetPacketIdDecimalInteger(): void
    {
        $this->packet->setPacketId(83);
        $this->assertSame(83, $this->packet->getPacketId(false));
    }

    public function testSetPacketIdDecimalString(): void
    {
        $this->packet->setPacketId('83');
        $this->assertSame(83, $this->packet->getPacketId(false));
    }

    public function testSetPacketIdHexWith0xPrefix(): void
    {
        $this->packet->setPacketId('0x53');
        $this->assertSame(0x53, $this->packet->getPacketId(false));
    }

    public function testSetPacketIdHexWithAmpersandH(): void
    {
        $this->packet->setPacketId('&h53');
        $this->assertSame(0x53, $this->packet->getPacketId(false));
    }

    public function testSetPacketIdHexWithAmpersandHUppercase(): void
    {
        $this->packet->setPacketId('&H53');
        $this->assertSame(0x53, $this->packet->getPacketId(false));
    }

    public function testSetPacketIdBinaryWithAmpersandB(): void
    {
        $this->packet->setPacketId('&b01010011');
        $this->assertSame(0x53, $this->packet->getPacketId(false));
    }

    public function testSetPacketIdOctalWithAmpersandO(): void
    {
        $this->packet->setPacketId('&o123');
        $this->assertSame(octdec('123'), $this->packet->getPacketId(false));
    }

    public function testSetPacketIdOctalWithLeadingZero(): void
    {
        $this->packet->setPacketId('0123');
        $this->assertSame(octdec('123'), $this->packet->getPacketId(false));
    }

    public function testSetPacketIdFloatThrowsInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->packet->setPacketId(3.14);
    }

    public function testSetPacketIdAboveMaxThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->packet->setPacketId(Packet::MAX_PACKET_ID + 1);
    }

    // setBrief / setName / setRemarks length limits

    public function testSetBriefTooLongThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->packet->setBrief(str_repeat('x', Packet::MAX_BRIEF + 1));
    }

    public function testSetNameEmptyWithoutIgnoreEmptyThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->packet->setName('');
    }

    public function testSetNameEmptyWithIgnoreEmptyIsAllowed(): void
    {
        $this->packet->setName('', true);
        $this->assertSame('', $this->packet->getName());
    }

    public function testSetRemarksAtMaxLengthIsAllowed(): void
    {
        $this->packet->setRemarks(str_repeat('x', Packet::MAX_REMARKS));
        $this->assertSame(Packet::MAX_REMARKS, strlen($this->packet->getRemarks(false)));
    }

    public function testSetRemarksTooLongThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->packet->setRemarks(str_repeat('x', Packet::MAX_REMARKS + 1));
    }

    // incrementEdited

    public function testIncrementEditedUpdatesCount(): void
    {
        $this->packet->incrementEdited();
        $this->assertSame(1, $this->packet->getEditedCount());
    }

    public function testIncrementEditedSetsEditedDateTime(): void
    {
        $this->packet->incrementEdited();
        $this->assertNotNull($this->packet->getEditedDateTime());
    }

    // getPublishedDateTime fallback

    public function testGetPublishedDateTimeReturnsCreatedWhenNotEdited(): void
    {
        $this->assertSame(
            $this->packet->getCreatedDateTime(),
            $this->packet->getPublishedDateTime()
        );
    }

    public function testGetPublishedDateTimeReturnsEditedDateTimeAfterEdit(): void
    {
        $this->packet->incrementEdited();
        $this->assertSame(
            $this->packet->getEditedDateTime(),
            $this->packet->getPublishedDateTime()
        );
    }

    // jsonSerialize

    public function testJsonSerializeHasExpectedKeys(): void
    {
        $data = $this->packet->jsonSerialize();
        foreach ([
            'created_datetime', 'edited_count', 'edited_datetime', 'id',
            'options_bitmask', 'packet_application_layer_id', 'packet_brief',
            'packet_direction_id', 'packet_format', 'packet_id', 'packet_name',
            'packet_remarks', 'packet_transport_layer_id', 'user_id',
        ] as $key) {
            $this->assertArrayHasKey($key, $data, "Missing key: $key");
        }
    }
}
