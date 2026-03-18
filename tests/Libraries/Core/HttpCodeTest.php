<?php

namespace BNETDocs\Tests\Libraries\Core;

use \BNETDocs\Libraries\Core\HttpCode;
use \OutOfBoundsException;
use \PHPUnit\Framework\TestCase;
use \UnexpectedValueException;

class HttpCodeTest extends TestCase
{
    // codeFromInt

    public function testCodeFromIntOk(): void
    {
        $this->assertSame('Ok', HttpCode::codeFromInt(200));
    }

    public function testCodeFromIntNotFound(): void
    {
        $this->assertSame('Not Found', HttpCode::codeFromInt(404));
    }

    public function testCodeFromIntInternalServerError(): void
    {
        $this->assertSame('Internal Server Error', HttpCode::codeFromInt(500));
    }

    public function testCodeFromIntUnknownThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        HttpCode::codeFromInt(999);
    }

    // codeFromString

    public function testCodeFromStringOk(): void
    {
        $this->assertSame(200, HttpCode::codeFromString('OK'));
    }

    public function testCodeFromStringNotFound(): void
    {
        $this->assertSame(404, HttpCode::codeFromString('NOT_FOUND'));
    }

    public function testCodeFromStringStripsHttpPrefix(): void
    {
        $this->assertSame(403, HttpCode::codeFromString('HTTP_FORBIDDEN'));
    }

    public function testCodeFromStringAlias(): void
    {
        $this->assertSame(403, HttpCode::codeFromString('ACCESS_DENIED'));
    }

    public function testCodeFromStringUnknownThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        HttpCode::codeFromString('MADE_UP_CODE');
    }

    /**
     * HttpCode.php:173 has a semicolon instead of a colon after the case label,
     * making 'PAYLOAD_TOO_LARGE' a no-op that falls through to default.
     * This test documents the correct expected behavior and will fail until fixed.
     */
    public function testCodeFromStringPayloadTooLarge(): void
    {
        $this->assertSame(413, HttpCode::codeFromString('PAYLOAD_TOO_LARGE'));
    }

    // isRedirectFromCode

    public function testIsRedirectMovedPermanently(): void
    {
        $this->assertTrue(HttpCode::isRedirectFromCode(301));
    }

    public function testIsRedirectFound(): void
    {
        $this->assertTrue(HttpCode::isRedirectFromCode(302));
    }

    public function testIsRedirectPermanentRedirect(): void
    {
        $this->assertTrue(HttpCode::isRedirectFromCode(308));
    }

    public function testIsRedirectOkIsFalse(): void
    {
        $this->assertFalse(HttpCode::isRedirectFromCode(200));
    }

    public function testIsRedirectNotFoundIsFalse(): void
    {
        $this->assertFalse(HttpCode::isRedirectFromCode(404));
    }

    public function testIsRedirectRangeCheck(): void
    {
        $this->assertTrue(HttpCode::isRedirectFromCode(350));
    }

    public function testIsRedirectRangeCheckDisabled(): void
    {
        $this->assertFalse(HttpCode::isRedirectFromCode(350, false));
    }

    // setCode / constructor

    public function testConstructorWithInt(): void
    {
        $http = new HttpCode(200);
        $this->assertSame(200, $http->getCode());
    }

    public function testConstructorWithString(): void
    {
        $http = new HttpCode('OK');
        $this->assertSame(200, $http->getCode());
    }

    public function testSetCodeOutOfBoundsThrows(): void
    {
        $this->expectException(OutOfBoundsException::class);
        new HttpCode(99);
    }
}
