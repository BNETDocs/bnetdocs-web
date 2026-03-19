<?php

namespace BNETDocs\Tests\Libraries\Discord;

use \BNETDocs\Libraries\Discord\EmbedAuthor;
use \LengthException;
use \PHPUnit\Framework\TestCase;

class EmbedAuthorTest extends TestCase
{
    // Constructor

    public function testConstructorSetsName(): void
    {
        $a = new EmbedAuthor('BNETDocs');
        $data = $a->jsonSerialize();
        $this->assertSame('BNETDocs', $data['name']);
    }

    public function testConstructorOptionalUrlAndIconUrl(): void
    {
        $a = new EmbedAuthor('BNETDocs', 'https://example.com', 'https://example.com/icon.png');
        $data = $a->jsonSerialize();
        $this->assertSame('https://example.com', $data['url']);
        $this->assertSame('https://example.com/icon.png', $data['icon_url']);
    }

    // setName

    public function testSetNameEmptyThrowsLengthException(): void
    {
        $this->expectException(LengthException::class);
        new EmbedAuthor('');
    }

    public function testSetNameTooLongThrowsLengthException(): void
    {
        $this->expectException(LengthException::class);
        new EmbedAuthor(str_repeat('x', EmbedAuthor::MAX_NAME + 1));
    }

    public function testSetNameAtMaxLengthIsAllowed(): void
    {
        $a = new EmbedAuthor(str_repeat('x', EmbedAuthor::MAX_NAME));
        $data = $a->jsonSerialize();
        $this->assertSame(EmbedAuthor::MAX_NAME, strlen($data['name']));
    }

    // jsonSerialize — empty optional fields are stripped

    public function testJsonSerializeOmitsEmptyUrl(): void
    {
        $a = new EmbedAuthor('Name');
        $data = $a->jsonSerialize();
        $this->assertArrayNotHasKey('url', $data);
    }

    public function testJsonSerializeOmitsEmptyIconUrl(): void
    {
        $a = new EmbedAuthor('Name');
        $data = $a->jsonSerialize();
        $this->assertArrayNotHasKey('icon_url', $data);
    }

    public function testJsonSerializeOmitsEmptyProxyIconUrl(): void
    {
        $a = new EmbedAuthor('Name');
        $data = $a->jsonSerialize();
        $this->assertArrayNotHasKey('proxy_icon_url', $data);
    }

    public function testJsonSerializeAlwaysIncludesName(): void
    {
        $a = new EmbedAuthor('Name');
        $data = $a->jsonSerialize();
        $this->assertArrayHasKey('name', $data);
    }

    // Constants

    public function testMaxNameConstant(): void
    {
        $this->assertSame(256, EmbedAuthor::MAX_NAME);
    }
}
