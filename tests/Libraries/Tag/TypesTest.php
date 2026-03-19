<?php

namespace BNETDocs\Tests\Libraries\Tag;

use \BNETDocs\Libraries\Tag\Types;
use \PHPUnit\Framework\TestCase;
use \ValueError;

class TypesTest extends TestCase
{
    // fromInt — valid values

    public function testFromIntComment(): void
    {
        $this->assertSame(Types::Comment, Types::fromInt(0));
    }

    public function testFromIntDocument(): void
    {
        $this->assertSame(Types::Document, Types::fromInt(1));
    }

    public function testFromIntNewsPost(): void
    {
        $this->assertSame(Types::NewsPost, Types::fromInt(2));
    }

    public function testFromIntPacket(): void
    {
        $this->assertSame(Types::Packet, Types::fromInt(3));
    }

    public function testFromIntServer(): void
    {
        $this->assertSame(Types::Server, Types::fromInt(4));
    }

    public function testFromIntUser(): void
    {
        $this->assertSame(Types::User, Types::fromInt(5));
    }

    // fromInt — invalid value

    public function testFromIntInvalidThrowsValueError(): void
    {
        $this->expectException(ValueError::class);
        Types::fromInt(99);
    }

    public function testFromIntNegativeThrowsValueError(): void
    {
        $this->expectException(ValueError::class);
        Types::fromInt(-1);
    }

    // toInt — each case

    public function testToIntComment(): void
    {
        $this->assertSame(0, Types::Comment->toInt());
    }

    public function testToIntDocument(): void
    {
        $this->assertSame(1, Types::Document->toInt());
    }

    public function testToIntNewsPost(): void
    {
        $this->assertSame(2, Types::NewsPost->toInt());
    }

    public function testToIntPacket(): void
    {
        $this->assertSame(3, Types::Packet->toInt());
    }

    public function testToIntServer(): void
    {
        $this->assertSame(4, Types::Server->toInt());
    }

    public function testToIntUser(): void
    {
        $this->assertSame(5, Types::User->toInt());
    }

    // Round-trip: fromInt → toInt

    public function testRoundTripAllCases(): void
    {
        foreach (range(0, 5) as $i)
        {
            $this->assertSame($i, Types::fromInt($i)->toInt());
        }
    }

    // Backed enum: ->value matches toInt()

    public function testBackedValueMatchesToInt(): void
    {
        foreach (Types::cases() as $case)
        {
            $this->assertSame($case->value, $case->toInt());
        }
    }

    // All cases are covered by the enum

    public function testCasesCount(): void
    {
        $this->assertCount(6, Types::cases());
    }
}
