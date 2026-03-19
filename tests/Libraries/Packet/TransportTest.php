<?php

namespace BNETDocs\Tests\Libraries\Packet;

use \BNETDocs\Libraries\Packet\Transport;
use \OutOfBoundsException;
use \PHPUnit\Framework\TestCase;

class TransportTest extends TestCase
{
    // Constructor — known IDs

    public function testIdOneTcp(): void
    {
        $t = new Transport(1);
        $this->assertSame(1, $t->getId());
        $this->assertSame('Transmission Control Protocol', $t->getLabel());
        $this->assertSame('TCP', $t->getTag());
    }

    public function testIdTwoUdp(): void
    {
        $t = new Transport(2);
        $this->assertSame(2, $t->getId());
        $this->assertSame('User Datagram Protocol', $t->getLabel());
        $this->assertSame('UDP', $t->getTag());
    }

    public function testIdThreeIcmp(): void
    {
        $t = new Transport(3);
        $this->assertSame(3, $t->getId());
        $this->assertSame('Internet Control Message Protocol', $t->getLabel());
        $this->assertSame('ICMP', $t->getTag());
    }

    // Constructor — invalid IDs

    public function testZeroIdThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        new Transport(0);
    }

    public function testOutOfRangeIdThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        new Transport(4);
    }

    public function testNegativeIdThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        new Transport(-1);
    }

    // getAllAsArray

    public function testGetAllAsArrayHasThreeEntries(): void
    {
        $this->assertCount(3, Transport::getAllAsArray());
    }

    public function testGetAllAsArrayKeysAreIntegers(): void
    {
        foreach (array_keys(Transport::getAllAsArray()) as $key)
        {
            $this->assertIsInt($key);
        }
    }

    // getAllAsObjects

    public function testGetAllAsObjectsHasThreeEntries(): void
    {
        $this->assertCount(3, Transport::getAllAsObjects());
    }

    public function testGetAllAsObjectsReturnsTransportInstances(): void
    {
        foreach (Transport::getAllAsObjects() as $obj)
        {
            $this->assertInstanceOf(Transport::class, $obj);
        }
    }

    public function testGetAllAsObjectsIdsMatchTable(): void
    {
        $table = Transport::getAllAsArray();
        foreach (Transport::getAllAsObjects() as $obj)
        {
            $this->assertArrayHasKey($obj->getId(), $table);
        }
    }
}
