<?php

namespace BNETDocs\Tests\Libraries\Server;

use \BNETDocs\Libraries\Server\Server;
use \OutOfBoundsException;
use \PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{
    private Server $server;

    protected function setUp(): void
    {
        // null id → allocate() returns true early; no database access.
        $this->server = new Server(null);
    }

    // Default state

    public function testDefaultIdIsNull(): void
    {
        $this->assertNull($this->server->getId());
    }

    public function testDefaultAddressIsEmpty(): void
    {
        $this->assertSame('', $this->server->getAddress());
    }

    public function testDefaultPortIsZero(): void
    {
        $this->assertSame(0, $this->server->getPort());
    }

    public function testDefaultStatusBitmaskIsDisabled(): void
    {
        $this->assertSame(Server::STATUS_DISABLED, $this->server->getStatusBitmask());
    }

    public function testDefaultTypeIdIsZero(): void
    {
        $this->assertSame(0, $this->server->getTypeId());
    }

    public function testDefaultLabelIsNull(): void
    {
        $this->assertNull($this->server->getLabel());
    }

    public function testDefaultUpdatedDateTimeIsNull(): void
    {
        $this->assertNull($this->server->getUpdatedDateTime());
    }

    public function testDefaultUserIdIsNull(): void
    {
        $this->assertNull($this->server->getUserId());
    }

    // Status bitmask helpers — default is STATUS_DISABLED only

    public function testDefaultIsOnlineFalse(): void
    {
        $this->assertFalse($this->server->isOnline());
    }

    public function testDefaultIsOfflineTrue(): void
    {
        $this->assertTrue($this->server->isOffline());
    }

    public function testDefaultIsDisabledTrue(): void
    {
        $this->assertTrue($this->server->isDisabled());
    }

    public function testDefaultIsEnabledFalse(): void
    {
        $this->assertFalse($this->server->isEnabled());
    }

    // setOnline / setOffline

    public function testSetOnlineTrueMakesOnline(): void
    {
        $this->server->setOnline(true);
        $this->assertTrue($this->server->isOnline());
        $this->assertFalse($this->server->isOffline());
    }

    public function testSetOfflineTrueMakesOffline(): void
    {
        $this->server->setOnline(true);
        $this->server->setOffline(true);
        $this->assertFalse($this->server->isOnline());
        $this->assertTrue($this->server->isOffline());
    }

    // setEnabled / setDisabled

    public function testSetEnabledTrueMakesEnabled(): void
    {
        $this->server->setEnabled(true);
        $this->assertTrue($this->server->isEnabled());
        $this->assertFalse($this->server->isDisabled());
    }

    public function testSetDisabledTrueMakesDisabled(): void
    {
        $this->server->setEnabled(true);
        $this->server->setDisabled(true);
        $this->assertTrue($this->server->isDisabled());
        $this->assertFalse($this->server->isEnabled());
    }

    // getName() fallback logic

    public function testGetNameWithLabelUsesLabel(): void
    {
        $this->server->setLabel('My Server');
        $this->assertSame('My Server', $this->server->getName());
    }

    public function testGetNameWithoutLabelFallsBackToAddressPort(): void
    {
        $this->server->setAddress('play.example.com');
        $this->server->setPort(6112);
        // Label is null (empty), so getName() uses address:port
        $this->assertSame('play.example.com:6112', $this->server->getName());
    }

    // setAddress validation

    public function testSetAddressEmptyWithoutAllowEmptyThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->server->setAddress('');
    }

    public function testSetAddressEmptyWithAllowEmptyIsAllowed(): void
    {
        $this->server->setAddress('', true);
        $this->assertSame('', $this->server->getAddress());
    }

    public function testSetAddressTooLongThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->server->setAddress(str_repeat('a', Server::MAX_ADDRESS + 1));
    }

    public function testSetAddressAtMaxLengthIsAllowed(): void
    {
        $value = str_repeat('a', Server::MAX_ADDRESS);
        $this->server->setAddress($value);
        $this->assertSame($value, $this->server->getAddress());
    }

    // setPort validation

    public function testSetPortZeroIsAllowed(): void
    {
        $this->server->setPort(0);
        $this->assertSame(0, $this->server->getPort());
    }

    public function testSetPortMaxIsAllowed(): void
    {
        $this->server->setPort(Server::MAX_PORT);
        $this->assertSame(Server::MAX_PORT, $this->server->getPort());
    }

    public function testSetPortNegativeThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->server->setPort(-1);
    }

    public function testSetPortAboveMaxThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->server->setPort(Server::MAX_PORT + 1);
    }

    // setStatusBitmask validation

    public function testSetStatusBitmaskZeroIsAllowed(): void
    {
        $this->server->setStatusBitmask(0);
        $this->assertSame(0, $this->server->getStatusBitmask());
    }

    public function testSetStatusBitmaskMaxIsAllowed(): void
    {
        $this->server->setStatusBitmask(Server::MAX_STATUS_BITMASK);
        $this->assertSame(Server::MAX_STATUS_BITMASK, $this->server->getStatusBitmask());
    }

    public function testSetStatusBitmaskAboveMaxThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->server->setStatusBitmask(Server::MAX_STATUS_BITMASK + 1);
    }

    // setLabel validation

    public function testSetLabelNullIsAllowed(): void
    {
        $this->server->setLabel(null);
        $this->assertNull($this->server->getLabel());
    }

    public function testSetLabelValidStringChangesValue(): void
    {
        $this->server->setLabel('Battle.net');
        $this->assertSame('Battle.net', $this->server->getLabel());
    }

    public function testSetLabelTooLongThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->server->setLabel(str_repeat('x', Server::MAX_LABEL + 1));
    }

    public function testSetLabelEmptyWithAutoNullBecomesNull(): void
    {
        // auto_null=true (default) converts empty string to null
        $this->server->setLabel('');
        $this->assertNull($this->server->getLabel());
    }

    // setTypeId validation

    public function testSetTypeIdZeroIsAllowed(): void
    {
        $this->server->setTypeId(0);
        $this->assertSame(0, $this->server->getTypeId());
    }

    public function testSetTypeIdNegativeThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->server->setTypeId(-1);
    }

    // DB-free early-exit methods

    public function testGetUriIsNullWhenIdIsNull(): void
    {
        $this->assertNull($this->server->getURI());
    }

    public function testGetTagsIsEmptyWhenIdIsNull(): void
    {
        $this->assertSame([], $this->server->getTags());
    }

    public function testDeallocateReturnsFalseWhenIdIsNull(): void
    {
        $this->assertFalse($this->server->deallocate());
    }

    // jsonSerialize

    public function testJsonSerializeHasExpectedKeys(): void
    {
        $data = $this->server->jsonSerialize();
        foreach ([
            'address', 'created_datetime', 'id', 'label', 'port',
            'status_bitmask', 'type_id', 'updated_datetime', 'uri', 'user_id',
        ] as $key) {
            $this->assertArrayHasKey($key, $data, "Missing key: $key");
        }
    }

    public function testJsonSerializeDefaultValues(): void
    {
        $data = $this->server->jsonSerialize();
        $this->assertNull($data['id']);
        $this->assertNull($data['label']);
        $this->assertNull($data['uri']);
        $this->assertNull($data['user_id']);
        $this->assertSame(0, $data['port']);
        $this->assertSame(Server::STATUS_DISABLED, $data['status_bitmask']);
    }
}
