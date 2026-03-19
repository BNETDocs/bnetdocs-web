<?php

namespace BNETDocs\Tests\Libraries\EventLog;

use \BNETDocs\Libraries\EventLog\Event;
use \BNETDocs\Libraries\EventLog\EventTypes;
use \LengthException;
use \OutOfBoundsException;
use \PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    private Event $event;

    protected function setUp(): void
    {
        // null id → allocate() returns true early without touching the database.
        $this->event = new Event(null);
    }

    // Default state after null construction

    public function testDefaultIdIsNull(): void
    {
        $this->assertNull($this->event->getId());
    }

    public function testDefaultTypeIdIsLogNote(): void
    {
        $this->assertSame(EventTypes::LOG_NOTE, $this->event->getTypeId());
    }

    public function testDefaultIpAddressIsNull(): void
    {
        $this->assertNull($this->event->getIPAddress());
    }

    public function testDefaultMetaDataIsNull(): void
    {
        $this->assertNull($this->event->getMetaData());
    }

    public function testDefaultUserIdIsNull(): void
    {
        $this->assertNull($this->event->getUserId());
    }

    public function testDefaultDateTimeIsNull(): void
    {
        $this->assertNull($this->event->getDateTime());
    }

    // getURI — null when id is null

    public function testGetUriIsNullWhenIdIsNull(): void
    {
        $this->assertNull($this->event->getURI());
    }

    // getTypeName — delegates to EventType::__toString()

    public function testGetTypeNameForLogNote(): void
    {
        $this->assertSame('Log Note', $this->event->getTypeName());
    }

    public function testGetTypeNameAfterTypeIdChange(): void
    {
        $this->event->setTypeId(EventTypes::USER_CREATED);
        $this->assertSame('User Created', $this->event->getTypeName());
    }

    // setId — bounds validation

    public function testSetIdNullIsAllowed(): void
    {
        $this->event->setId(null);
        $this->assertNull($this->event->getId());
    }

    public function testSetIdValidPositiveIsAllowed(): void
    {
        $this->event->setId(42);
        $this->assertSame(42, $this->event->getId());
    }

    public function testSetIdNegativeThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->event->setId(-1);
    }

    // setTypeId — bounds validation

    public function testSetTypeIdValidIsAllowed(): void
    {
        $this->event->setTypeId(EventTypes::PACKET_CREATED);
        $this->assertSame(EventTypes::PACKET_CREATED, $this->event->getTypeId());
    }

    public function testSetTypeIdNegativeThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->event->setTypeId(-1);
    }

    // setIPAddress — format and length validation

    public function testSetIPAddressValidIPv4(): void
    {
        $this->event->setIPAddress('127.0.0.1');
        $this->assertSame('127.0.0.1', $this->event->getIPAddress());
    }

    public function testSetIPAddressValidIPv6(): void
    {
        $this->event->setIPAddress('::1');
        $this->assertSame('::1', $this->event->getIPAddress());
    }

    public function testSetIPAddressFullIPv6(): void
    {
        $this->event->setIPAddress('2001:db8::1');
        $this->assertSame('2001:db8::1', $this->event->getIPAddress());
    }

    public function testSetIPAddressNullIsAllowed(): void
    {
        $this->event->setIPAddress('127.0.0.1');
        $this->event->setIPAddress(null);
        $this->assertNull($this->event->getIPAddress());
    }

    public function testSetIPAddressInvalidStringThrowsLengthException(): void
    {
        $this->expectException(LengthException::class);
        $this->event->setIPAddress('not-an-ip');
    }

    public function testSetIPAddressEmptyStringThrowsLengthException(): void
    {
        $this->expectException(LengthException::class);
        $this->event->setIPAddress('');
    }

    // setMetaData

    public function testSetMetaDataNullIsAllowed(): void
    {
        $this->event->setMetaData(null);
        $this->assertNull($this->event->getMetaData());
    }

    public function testSetMetaDataArrayIsAllowed(): void
    {
        $this->event->setMetaData(['key' => 'value']);
        $this->assertSame(['key' => 'value'], $this->event->getMetaData());
    }

    public function testSetMetaDataScalarIsAllowed(): void
    {
        $this->event->setMetaData('hello');
        $this->assertSame('hello', $this->event->getMetaData());
    }

    // setUserId

    public function testSetUserIdNullIsAllowed(): void
    {
        $this->event->setUserId(null);
        $this->assertNull($this->event->getUserId());
    }

    public function testSetUserIdPositiveIsAllowed(): void
    {
        $this->event->setUserId(99);
        $this->assertSame(99, $this->event->getUserId());
    }

    public function testSetUserIdNegativeThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->event->setUserId(-1);
    }

    // setDateTime

    public function testSetDateTimeFromString(): void
    {
        $this->event->setDateTime('2024-06-01 12:00:00');
        $this->assertNotNull($this->event->getDateTime());
        $this->assertSame('2024-06-01 12:00:00', $this->event->getDateTime()->format('Y-m-d H:i:s'));
    }

    public function testSetDateTimeNullClearsValue(): void
    {
        $this->event->setDateTime('2024-01-01');
        $this->event->setDateTime(null);
        $this->assertNull($this->event->getDateTime());
    }

    public function testSetDateTimeFromInterface(): void
    {
        $dt = new \DateTimeImmutable('2024-03-15 08:00:00', new \DateTimeZone('UTC'));
        $this->event->setDateTime($dt);
        $this->assertSame('2024-03-15', $this->event->getDateTime()->format('Y-m-d'));
    }

    // jsonSerialize

    public function testJsonSerializeHasExpectedKeys(): void
    {
        $data = $this->event->jsonSerialize();
        $this->assertArrayHasKey('datetime', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('ip_address', $data);
        $this->assertArrayHasKey('meta_data', $data);
        $this->assertArrayHasKey('type_id', $data);
        $this->assertArrayHasKey('user_id', $data);
    }

    public function testJsonSerializeDefaultValues(): void
    {
        $data = $this->event->jsonSerialize();
        $this->assertNull($data['id']);
        $this->assertNull($data['ip_address']);
        $this->assertNull($data['meta_data']);
        $this->assertSame(EventTypes::LOG_NOTE, $data['type_id']);
        $this->assertNull($data['user_id']);
    }
}
