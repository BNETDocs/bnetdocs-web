<?php

namespace BNETDocs\Tests\Libraries\Community;

use \BNETDocs\Libraries\Community\Credits;
use \PHPUnit\Framework\TestCase;

class CreditsTest extends TestCase
{
    // Constants

    public function testDefaultLimitIsCorrect(): void
    {
        $this->assertSame(5, Credits::DEFAULT_LIMIT);
    }

    public function testDefaultAnonymousIsCorrect(): void
    {
        $this->assertSame('Anonymous', Credits::DEFAULT_ANONYMOUS);
    }

    // Private constructor prevents instantiation

    public function testConstructorIsPrivate(): void
    {
        // PHP enforces visibility before executing the constructor body,
        // so calling a private constructor from outside the class throws
        // Error (not the LogicException defined inside the constructor).
        $this->expectException(\Error::class);
        $rc = new \ReflectionClass(Credits::class);
        $rc->newInstanceWithoutConstructor(); // this is fine; test the constructor directly:
        new Credits(); // @phpstan-ignore-line — intentionally testing private constructor
    }
}
