<?php

namespace BNETDocs\Tests\Libraries\Packet;

use \BNETDocs\Libraries\Packet\Application;
use \OutOfBoundsException;
use \PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    // Constructor — known IDs

    public function testIdOneBnetV1Tcp(): void
    {
        $a = new Application(1);
        $this->assertSame(1, $a->getId());
        $this->assertSame('Battle.net v1 TCP Messages', $a->getLabel());
        $this->assertSame('SID', $a->getTag());
    }

    public function testIdTwoBnetV1Udp(): void
    {
        $a = new Application(2);
        $this->assertSame(2, $a->getId());
        $this->assertSame('Battle.net v1 UDP Messages', $a->getLabel());
        $this->assertSame('PKT', $a->getTag());
    }

    public function testIdThreeDiabloIiRealm(): void
    {
        $a = new Application(3);
        $this->assertSame('Diablo II Realm Messages', $a->getLabel());
        $this->assertSame('MCP', $a->getTag());
    }

    public function testIdFourDiabloIiInGame(): void
    {
        $a = new Application(4);
        $this->assertSame('Diablo II In-Game Messages', $a->getLabel());
        $this->assertSame('D2GS', $a->getTag());
    }

    public function testIdFiveWarcraftIiiInGame(): void
    {
        $a = new Application(5);
        $this->assertSame('Warcraft III In-Game Messages', $a->getLabel());
        $this->assertSame('W3GS', $a->getTag());
    }

    public function testIdSixBotNet(): void
    {
        $a = new Application(6);
        $this->assertSame('BotNet Messages', $a->getLabel());
        $this->assertSame('PACKET', $a->getTag());
    }

    public function testIdSevenBnls(): void
    {
        $a = new Application(7);
        $this->assertSame('BNLS Messages', $a->getLabel());
        $this->assertSame('BNLS', $a->getTag());
    }

    public function testIdEightStarcraftInGame(): void
    {
        $a = new Application(8);
        $this->assertSame('Starcraft In-Game Messages', $a->getLabel());
        $this->assertSame('SCGP', $a->getTag());
    }

    public function testIdNineBnetV2Tcp(): void
    {
        $a = new Application(9);
        $this->assertSame(9, $a->getId());
        $this->assertSame('Battle.net v2 TCP Messages', $a->getLabel());
        $this->assertSame('SID2', $a->getTag());
    }

    // Constructor — invalid IDs

    public function testZeroIdThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        new Application(0);
    }

    public function testOutOfRangeIdThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        new Application(10);
    }

    public function testNegativeIdThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        new Application(-1);
    }

    // getAllAsArray

    public function testGetAllAsArrayHasNineEntries(): void
    {
        $this->assertCount(9, Application::getAllAsArray());
    }

    public function testGetAllAsArrayKeysAreIntegers(): void
    {
        $arr = Application::getAllAsArray();
        foreach (array_keys($arr) as $key)
        {
            $this->assertIsInt($key);
        }
    }

    // getAllAsObjects

    public function testGetAllAsObjectsHasNineEntries(): void
    {
        $this->assertCount(9, Application::getAllAsObjects());
    }

    public function testGetAllAsObjectsReturnsApplicationInstances(): void
    {
        foreach (Application::getAllAsObjects() as $obj)
        {
            $this->assertInstanceOf(Application::class, $obj);
        }
    }

    public function testGetAllAsObjectsIdsMatchTable(): void
    {
        $table = Application::getAllAsArray();
        foreach (Application::getAllAsObjects() as $obj)
        {
            $this->assertArrayHasKey($obj->getId(), $table);
        }
    }
}
