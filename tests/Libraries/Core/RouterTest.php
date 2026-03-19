<?php

namespace BNETDocs\Tests\Libraries\Core;

use \BNETDocs\Libraries\Core\Router;
use \Error;
use \PHPUnit\Framework\TestCase;
use \ReflectionClass;

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset the static query cache between tests so getenv changes take effect.
        $ref = new ReflectionClass(Router::class);
        $prop = $ref->getProperty('args');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    protected function tearDown(): void
    {
        // Restore any env vars we modified.
        putenv('REQUEST_METHOD');
        putenv('HTTP_HOST');
        putenv('SERVER_NAME');
        putenv('HOST');
        putenv('QUERY_STRING');
    }

    // Constructor

    public function testConstructorThrowsError(): void
    {
        // Constructor is private; PHP enforces visibility before the body runs.
        $this->expectException(Error::class);
        new Router();
    }

    // requestMethod

    public function testRequestMethodReturnsGetWhenSet(): void
    {
        putenv('REQUEST_METHOD=GET');
        $this->assertSame('GET', Router::requestMethod());
    }

    public function testRequestMethodReturnsPostWhenSet(): void
    {
        putenv('REQUEST_METHOD=POST');
        $this->assertSame('POST', Router::requestMethod());
    }

    public function testRequestMethodReturnsEmptyWhenUnset(): void
    {
        putenv('REQUEST_METHOD');
        $this->assertSame('', Router::requestMethod());
    }

    // serverName

    public function testServerNamePrefersHttpHost(): void
    {
        putenv('HTTP_HOST=bnetdocs.org');
        putenv('SERVER_NAME=fallback.example.com');
        $this->assertSame('bnetdocs.org', Router::serverName());
    }

    public function testServerNameFallsBackToServerName(): void
    {
        putenv('HTTP_HOST');
        putenv('SERVER_NAME=example.com');
        putenv('HOST');
        $this->assertSame('example.com', Router::serverName());
    }

    public function testServerNameFallsBackToHost(): void
    {
        putenv('HTTP_HOST');
        putenv('SERVER_NAME');
        putenv('HOST=host.example.com');
        $this->assertSame('host.example.com', Router::serverName());
    }

    public function testServerNameReturnsEmptyWhenNoneSet(): void
    {
        putenv('HTTP_HOST');
        putenv('SERVER_NAME');
        putenv('HOST');
        $this->assertSame('', Router::serverName());
    }

    // query — GET parameters from QUERY_STRING

    public function testQueryParsesQueryString(): void
    {
        putenv('REQUEST_METHOD=GET');
        putenv('QUERY_STRING=foo=bar&baz=qux');
        $args = Router::query();
        $this->assertSame('bar', $args['foo']);
        $this->assertSame('qux', $args['baz']);
    }

    public function testQueryReturnsEmptyArrayForEmptyQueryString(): void
    {
        putenv('REQUEST_METHOD=GET');
        putenv('QUERY_STRING=');
        $args = Router::query();
        $this->assertSame([], $args);
    }

    public function testQueryIsCachedOnSubsequentCalls(): void
    {
        putenv('REQUEST_METHOD=GET');
        putenv('QUERY_STRING=x=1');
        $first  = Router::query();
        putenv('QUERY_STRING=x=999'); // change env — cached result should ignore this
        $second = Router::query();
        $this->assertSame($first, $second);
    }
}
