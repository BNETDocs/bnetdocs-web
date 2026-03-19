<?php

namespace BNETDocs\Tests\Libraries\Discord;

use \BNETDocs\Libraries\Discord\EmbedImage;
use \BNETDocs\Libraries\Discord\EmbedThumbnail;
use \PHPUnit\Framework\TestCase;

class EmbedThumbnailTest extends TestCase
{
    public function testExtendsEmbedImage(): void
    {
        $t = new EmbedThumbnail('https://example.com/thumb.png');
        $this->assertInstanceOf(EmbedImage::class, $t);
    }

    public function testJsonSerializeIncludesUrl(): void
    {
        $t = new EmbedThumbnail('https://example.com/thumb.png');
        $data = $t->jsonSerialize();
        $this->assertSame('https://example.com/thumb.png', $data['url']);
    }

    public function testJsonSerializeWithDimensions(): void
    {
        $t = new EmbedThumbnail('https://example.com/thumb.png', 320, 240);
        $data = $t->jsonSerialize();
        $this->assertSame(320, $data['width']);
        $this->assertSame(240, $data['height']);
    }

    public function testJsonSerializeOmitsZeroDimensions(): void
    {
        $t = new EmbedThumbnail('https://example.com/thumb.png');
        $data = $t->jsonSerialize();
        $this->assertArrayNotHasKey('width', $data);
        $this->assertArrayNotHasKey('height', $data);
    }
}
