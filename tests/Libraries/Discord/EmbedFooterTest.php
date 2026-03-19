<?php

namespace BNETDocs\Tests\Libraries\Discord;

use \BNETDocs\Libraries\Discord\EmbedFooter;
use \LengthException;
use \PHPUnit\Framework\TestCase;

class EmbedFooterTest extends TestCase
{
    // Constructor

    public function testConstructorSetsText(): void
    {
        $f = new EmbedFooter('Footer text');
        $data = $f->jsonSerialize();
        $this->assertSame('Footer text', $data['text']);
    }

    public function testConstructorOptionalIconUrl(): void
    {
        $f = new EmbedFooter('Text', 'https://example.com/icon.png');
        $data = $f->jsonSerialize();
        $this->assertSame('https://example.com/icon.png', $data['icon_url']);
    }

    public function testConstructorOptionalProxyIconUrl(): void
    {
        $f = new EmbedFooter('Text', '', 'https://proxy.example.com/icon.png');
        $data = $f->jsonSerialize();
        $this->assertSame('https://proxy.example.com/icon.png', $data['proxy_icon_url']);
    }

    // setText

    public function testSetTextTooLongThrowsLengthException(): void
    {
        $this->expectException(LengthException::class);
        new EmbedFooter(str_repeat('x', EmbedFooter::MAX_TEXT + 1));
    }

    public function testSetTextAtMaxLengthIsAllowed(): void
    {
        $f = new EmbedFooter(str_repeat('x', EmbedFooter::MAX_TEXT));
        $data = $f->jsonSerialize();
        $this->assertSame(EmbedFooter::MAX_TEXT, strlen($data['text']));
    }

    // jsonSerialize — empty optional fields are stripped

    public function testJsonSerializeOmitsEmptyIconUrl(): void
    {
        $f = new EmbedFooter('Text');
        $data = $f->jsonSerialize();
        $this->assertArrayNotHasKey('icon_url', $data);
    }

    public function testJsonSerializeOmitsEmptyProxyIconUrl(): void
    {
        $f = new EmbedFooter('Text');
        $data = $f->jsonSerialize();
        $this->assertArrayNotHasKey('proxy_icon_url', $data);
    }

    public function testJsonSerializeIncludesNonEmptyText(): void
    {
        $f = new EmbedFooter('My Footer');
        $data = $f->jsonSerialize();
        $this->assertArrayHasKey('text', $data);
    }

    // Constants

    public function testMaxTextConstant(): void
    {
        $this->assertSame(2048, EmbedFooter::MAX_TEXT);
    }
}
