<?php

namespace BNETDocs\Tests\Libraries\User;

use \BNETDocs\Libraries\User\Gravatar;
use \PHPUnit\Framework\TestCase;
use \UnexpectedValueException;

class GravatarTest extends TestCase
{
    private const SAMPLE_EMAIL = 'user@example.com';

    // Constructor

    public function testConstructorSetsEmail(): void
    {
        $g = new Gravatar(self::SAMPLE_EMAIL);
        $this->assertSame(self::SAMPLE_EMAIL, $g->getEmail());
    }

    public function testConstructorWithInvalidEmailThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new Gravatar('not-an-email');
    }

    public function testConstructorWithEmptyStringThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new Gravatar('');
    }

    // setEmail

    public function testSetEmailChangesEmail(): void
    {
        $g = new Gravatar(self::SAMPLE_EMAIL);
        $g->setEmail('other@example.com');
        $this->assertSame('other@example.com', $g->getEmail());
    }

    public function testSetEmailWithInvalidEmailThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $g = new Gravatar(self::SAMPLE_EMAIL);
        $g->setEmail('bad');
    }

    // getHash

    public function testGetHashIsLowercaseMd5OfEmail(): void
    {
        $g = new Gravatar(self::SAMPLE_EMAIL);
        $expected = hash('md5', strtolower(trim(self::SAMPLE_EMAIL)));
        $this->assertSame($expected, $g->getHash());
    }

    public function testGetHashNormalizesEmailCase(): void
    {
        $lower = new Gravatar('user@example.com');
        $upper = new Gravatar('USER@example.com');
        $this->assertSame($lower->getHash(), $upper->getHash());
    }

    public function testGetHashNormalizesEmailWhitespace(): void
    {
        $trimmed = new Gravatar('user@example.com');
        // setEmail strips via trim() before hashing
        $this->assertSame(
            hash('md5', 'user@example.com'),
            $trimmed->getHash()
        );
    }

    // getUrl

    public function testGetUrlWithNoParamsContainsHash(): void
    {
        $g = new Gravatar(self::SAMPLE_EMAIL);
        $url = $g->getUrl();
        $this->assertStringContainsString($g->getHash(), $url);
        $this->assertStringContainsString(Gravatar::GRAVATAR_BASE_URL, $url);
        $this->assertStringNotContainsString('?', $url);
    }

    public function testGetUrlWithSize(): void
    {
        $g = new Gravatar(self::SAMPLE_EMAIL);
        $url = $g->getUrl(80);
        $this->assertStringContainsString('s=80', $url);
    }

    public function testGetUrlWithDefault(): void
    {
        $g = new Gravatar(self::SAMPLE_EMAIL);
        $url = $g->getUrl(null, 'mp');
        $this->assertStringContainsString('d=mp', $url);
    }

    public function testGetUrlWithForceDefault(): void
    {
        $g = new Gravatar(self::SAMPLE_EMAIL);
        $url = $g->getUrl(null, null, 'y');
        $this->assertStringContainsString('f=y', $url);
    }

    public function testGetUrlWithRating(): void
    {
        $g = new Gravatar(self::SAMPLE_EMAIL);
        $url = $g->getUrl(null, null, null, 'pg');
        $this->assertStringContainsString('r=pg', $url);
    }

    public function testGetUrlWithAllParams(): void
    {
        $g = new Gravatar(self::SAMPLE_EMAIL);
        $url = $g->getUrl(100, 'identicon', 'y', 'g');
        $this->assertStringContainsString('s=100', $url);
        $this->assertStringContainsString('d=identicon', $url);
        $this->assertStringContainsString('f=y', $url);
        $this->assertStringContainsString('r=g', $url);
    }

    // jsonSerialize

    public function testJsonSerializeHasRequiredKeys(): void
    {
        $g = new Gravatar(self::SAMPLE_EMAIL);
        $data = $g->jsonSerialize();
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('hash', $data);
        $this->assertArrayHasKey('url', $data);
    }

    public function testJsonSerializeValues(): void
    {
        $g = new Gravatar(self::SAMPLE_EMAIL);
        $data = $g->jsonSerialize();
        $this->assertSame(self::SAMPLE_EMAIL, $data['email']);
        $this->assertSame($g->getHash(), $data['hash']);
        $this->assertSame($g->getUrl(), $data['url']);
    }
}
