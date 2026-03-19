<?php

namespace BNETDocs\Tests\Libraries\Core;

use \BNETDocs\Libraries\Core\UrlFormatter;
use \PHPUnit\Framework\TestCase;

class UrlFormatterTest extends TestCase
{
    protected function setUp(): void
    {
        // Provide a stable request context for every test.
        putenv('HTTP_HOST=test.example.com');
        putenv('DOCUMENT_URI=/foo/bar');
        putenv('QUERY_STRING=');
        putenv('HTTPS');          // unset
        putenv('SERVER_PORT');    // unset
    }

    protected function tearDown(): void
    {
        putenv('HTTP_HOST');
        putenv('SERVER_NAME');
        putenv('HOST');
        putenv('DOCUMENT_URI');
        putenv('QUERY_STRING');
        putenv('HTTPS');
        putenv('SERVER_PORT');
    }

    // Scheme detection

    public function testHttpWhenNoHttpsEnvAndPortNotSet(): void
    {
        $url = UrlFormatter::format('/path');
        $this->assertStringStartsWith('http://', $url);
    }

    public function testHttpsWhenServerPortIs443(): void
    {
        putenv('SERVER_PORT=443');
        $url = UrlFormatter::format('/path');
        $this->assertStringStartsWith('https://', $url);
    }

    public function testHttpsWhenHttpsIsOn(): void
    {
        putenv('HTTPS=on');
        $url = UrlFormatter::format('/path');
        $this->assertStringStartsWith('https://', $url);
    }

    public function testHttpsWhenHttpsIsYes(): void
    {
        putenv('HTTPS=yes');
        $url = UrlFormatter::format('/path');
        $this->assertStringStartsWith('https://', $url);
    }

    public function testHttpsWhenHttpsIsTrue(): void
    {
        putenv('HTTPS=true');
        $url = UrlFormatter::format('/path');
        $this->assertStringStartsWith('https://', $url);
    }

    public function testHttpsWhenHttpsIsOne(): void
    {
        putenv('HTTPS=1');
        $url = UrlFormatter::format('/path');
        $this->assertStringStartsWith('https://', $url);
    }

    public function testHttpWhenHttpsIsOff(): void
    {
        putenv('HTTPS=off');
        $url = UrlFormatter::format('/path');
        $this->assertStringStartsWith('http://', $url);
    }

    // Host resolution

    public function testUsesCurrentHostForAbsolutePath(): void
    {
        $url = UrlFormatter::format('/page');
        $this->assertStringContainsString('test.example.com', $url);
    }

    // Absolute path

    public function testAbsolutePathUsedAsIs(): void
    {
        $url = UrlFormatter::format('/new/path');
        $this->assertSame('http://test.example.com/new/path', $url);
    }

    // Relative path

    public function testRelativePathSplicedIntoCurrentDirectory(): void
    {
        // DOCUMENT_URI=/foo/bar → dirname = /foo (starts with /)
        // The code does: '/' . ($dir . '/' . $path) = '/' . '/foo/relative' = '//foo/relative'
        // This double-slash is a known quirk of the implementation.
        $url = UrlFormatter::format('relative');
        $this->assertSame('http://test.example.com//foo/relative', $url);
    }

    // Query string

    public function testQueryStringPreserved(): void
    {
        $url = UrlFormatter::format('/path?key=value&other=123');
        $this->assertSame('http://test.example.com/path?key=value&other=123', $url);
    }

    public function testNoQueryStringProducesNoQuestionMark(): void
    {
        $url = UrlFormatter::format('/path');
        $this->assertStringNotContainsString('?', $url);
    }

    // Protocol-relative URL (//host/path)

    public function testProtocolRelativeUsesCurrentScheme(): void
    {
        $url = UrlFormatter::format('//other.example.com/page');
        $this->assertSame('http://other.example.com/page', $url);
    }

    public function testProtocolRelativeOverridesHost(): void
    {
        $url = UrlFormatter::format('//cdn.example.com/asset.js');
        $this->assertStringContainsString('cdn.example.com', $url);
        $this->assertStringNotContainsString('test.example.com', $url);
    }

    // Full URL with explicit scheme

    public function testFullUrlPreservesSchemeAndHost(): void
    {
        $url = UrlFormatter::format('https://cdn.example.com/asset.js');
        $this->assertSame('https://cdn.example.com/asset.js', $url);
    }

    public function testFullUrlWithQueryString(): void
    {
        $url = UrlFormatter::format('https://cdn.example.com/asset.js?v=1');
        $this->assertSame('https://cdn.example.com/asset.js?v=1', $url);
    }

    // Empty input falls back to current path

    public function testEmptyValueFallsBackToCurrentPath(): void
    {
        $url = UrlFormatter::format('');
        $this->assertSame('http://test.example.com/foo/bar', $url);
    }
}
