<?php

namespace BNETDocs\Tests\Libraries\Core;

use \BNETDocs\Libraries\Core\DateTime;
use \DateTimeInterface;
use \DateTimeZone;
use \PHPUnit\Framework\TestCase;

class DateTimeTest extends TestCase
{
    private DateTime $dt;

    protected function setUp(): void
    {
        $this->dt = new DateTime('2024-01-15 12:00:00', new DateTimeZone('UTC'));
    }

    // jsonSerialize

    public function testJsonSerializeHasRequiredKeys(): void
    {
        $data = $this->dt->jsonSerialize();
        $this->assertArrayHasKey('iso', $data);
        $this->assertArrayHasKey('tz', $data);
        $this->assertArrayHasKey('unix', $data);
    }

    public function testJsonSerializeIsoIsRfc2822(): void
    {
        $data = $this->dt->jsonSerialize();
        $this->assertSame($this->dt->format(DateTimeInterface::RFC2822), $data['iso']);
    }

    public function testJsonSerializeTzIsTimezoneName(): void
    {
        $data = $this->dt->jsonSerialize();
        $this->assertSame($this->dt->format('e'), $data['tz']);
    }

    public function testJsonSerializeUnixIsInt(): void
    {
        $data = $this->dt->jsonSerialize();
        $this->assertIsInt($data['unix']);
    }

    public function testJsonSerializeUnixMatchesTimestamp(): void
    {
        $data = $this->dt->jsonSerialize();
        $this->assertSame((int) $this->dt->format('U'), $data['unix']);
    }

    public function testJsonSerializeUtcTimezone(): void
    {
        $data = $this->dt->jsonSerialize();
        $this->assertSame('UTC', $data['tz']);
    }

    public function testJsonSerializeNonUtcTimezone(): void
    {
        $dt = new DateTime('2024-06-01 00:00:00', new DateTimeZone('America/New_York'));
        $data = $dt->jsonSerialize();
        $this->assertSame('America/New_York', $data['tz']);
    }

    // __toString

    public function testToStringFormat(): void
    {
        $expected = sprintf('%d %s', $this->dt->format('U'), $this->dt->format(DateTimeInterface::RFC2822));
        $this->assertSame($expected, (string) $this->dt);
    }

    public function testToStringStartsWithUnixTimestamp(): void
    {
        $str = (string) $this->dt;
        $parts = explode(' ', $str, 2);
        $this->assertSame($this->dt->format('U'), $parts[0]);
    }

    public function testToStringContainsRfc2822(): void
    {
        $str = (string) $this->dt;
        $this->assertStringContainsString($this->dt->format(DateTimeInterface::RFC2822), $str);
    }

    // Extends \DateTime

    public function testExtendsPhpDateTime(): void
    {
        $this->assertInstanceOf(\DateTime::class, $this->dt);
    }

    public function testImplementsJsonSerializable(): void
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->dt);
    }
}
