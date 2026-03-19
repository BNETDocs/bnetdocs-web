<?php

namespace BNETDocs\Tests\Libraries\Discord;

use \BNETDocs\Libraries\Discord\EmbedImage;
use \PHPUnit\Framework\TestCase;

class EmbedImageTest extends TestCase
{
    // Constructor

    public function testConstructorSetsUrl(): void
    {
        $img = new EmbedImage('https://example.com/img.png');
        $data = $img->jsonSerialize();
        $this->assertSame('https://example.com/img.png', $data['url']);
    }

    public function testConstructorOptionalDimensions(): void
    {
        $img = new EmbedImage('https://example.com/img.png', 640, 480);
        $data = $img->jsonSerialize();
        $this->assertSame(640, $data['width']);
        $this->assertSame(480, $data['height']);
    }

    public function testConstructorOptionalProxyUrl(): void
    {
        $img = new EmbedImage('https://example.com/img.png', 0, 0, 'https://proxy.example.com/img.png');
        $data = $img->jsonSerialize();
        $this->assertSame('https://proxy.example.com/img.png', $data['proxy_url']);
    }

    // jsonSerialize — empty() strips zero integers and empty strings

    public function testJsonSerializeOmitsZeroWidth(): void
    {
        // empty(0) === true, so zero width is filtered out
        $img = new EmbedImage('https://example.com/img.png', 0, 0);
        $data = $img->jsonSerialize();
        $this->assertArrayNotHasKey('width', $data);
    }

    public function testJsonSerializeOmitsZeroHeight(): void
    {
        $img = new EmbedImage('https://example.com/img.png', 0, 0);
        $data = $img->jsonSerialize();
        $this->assertArrayNotHasKey('height', $data);
    }

    public function testJsonSerializeOmitsEmptyProxyUrl(): void
    {
        $img = new EmbedImage('https://example.com/img.png');
        $data = $img->jsonSerialize();
        $this->assertArrayNotHasKey('proxy_url', $data);
    }

    public function testJsonSerializeIncludesNonZeroDimensions(): void
    {
        $img = new EmbedImage('https://example.com/img.png', 100, 200);
        $data = $img->jsonSerialize();
        $this->assertArrayHasKey('width', $data);
        $this->assertArrayHasKey('height', $data);
    }

    public function testJsonSerializeAlwaysIncludesUrl(): void
    {
        $img = new EmbedImage('https://example.com/img.png');
        $data = $img->jsonSerialize();
        $this->assertArrayHasKey('url', $data);
    }
}
