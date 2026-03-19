<?php

namespace BNETDocs\Tests\Libraries\Core;

use \BNETDocs\Libraries\Core\ExceptionHandler;
use \Error;
use \PHPUnit\Framework\TestCase;

class ExceptionHandlerTest extends TestCase
{
    // Constructor

    public function testConstructorThrowsError(): void
    {
        // Constructor is private; PHP enforces visibility before the body runs.
        $this->expectException(Error::class);
        new ExceptionHandler();
    }

    // phpErrorName — known constants

    public function testPhpErrorNameEError(): void
    {
        $this->assertSame('E_ERROR', ExceptionHandler::phpErrorName(E_ERROR));
    }

    public function testPhpErrorNameEWarning(): void
    {
        $this->assertSame('E_WARNING', ExceptionHandler::phpErrorName(E_WARNING));
    }

    public function testPhpErrorNameEParse(): void
    {
        $this->assertSame('E_PARSE', ExceptionHandler::phpErrorName(E_PARSE));
    }

    public function testPhpErrorNameENotice(): void
    {
        $this->assertSame('E_NOTICE', ExceptionHandler::phpErrorName(E_NOTICE));
    }

    public function testPhpErrorNameECoreError(): void
    {
        $this->assertSame('E_CORE_ERROR', ExceptionHandler::phpErrorName(E_CORE_ERROR));
    }

    public function testPhpErrorNameECoreWarning(): void
    {
        $this->assertSame('E_CORE_WARNING', ExceptionHandler::phpErrorName(E_CORE_WARNING));
    }

    public function testPhpErrorNameECompileError(): void
    {
        $this->assertSame('E_COMPILE_ERROR', ExceptionHandler::phpErrorName(E_COMPILE_ERROR));
    }

    public function testPhpErrorNameECompileWarning(): void
    {
        $this->assertSame('E_COMPILE_WARNING', ExceptionHandler::phpErrorName(E_COMPILE_WARNING));
    }

    public function testPhpErrorNameEUserError(): void
    {
        $this->assertSame('E_USER_ERROR', ExceptionHandler::phpErrorName(E_USER_ERROR));
    }

    public function testPhpErrorNameEUserWarning(): void
    {
        $this->assertSame('E_USER_WARNING', ExceptionHandler::phpErrorName(E_USER_WARNING));
    }

    public function testPhpErrorNameEUserNotice(): void
    {
        $this->assertSame('E_USER_NOTICE', ExceptionHandler::phpErrorName(E_USER_NOTICE));
    }

    public function testPhpErrorNameEStrict(): void
    {
        // E_STRICT (2048) is deprecated as a constant in PHP 8.4; use the integer value directly.
        $this->assertSame('E_STRICT', ExceptionHandler::phpErrorName(2048));
    }

    public function testPhpErrorNameERecoverableError(): void
    {
        $this->assertSame('E_RECOVERABLE_ERROR', ExceptionHandler::phpErrorName(E_RECOVERABLE_ERROR));
    }

    public function testPhpErrorNameEDeprecated(): void
    {
        $this->assertSame('E_DEPRECATED', ExceptionHandler::phpErrorName(E_DEPRECATED));
    }

    public function testPhpErrorNameEUserDeprecated(): void
    {
        $this->assertSame('E_USER_DEPRECATED', ExceptionHandler::phpErrorName(E_USER_DEPRECATED));
    }

    public function testPhpErrorNameEAll(): void
    {
        $this->assertSame('E_ALL', ExceptionHandler::phpErrorName(E_ALL));
    }

    // phpErrorName — unknown value

    public function testPhpErrorNameUnknownReturnsEUnknown(): void
    {
        $this->assertSame('E_UNKNOWN', ExceptionHandler::phpErrorName(99999));
    }

    public function testPhpErrorNameZeroReturnsEUnknown(): void
    {
        $this->assertSame('E_UNKNOWN', ExceptionHandler::phpErrorName(0));
    }
}
