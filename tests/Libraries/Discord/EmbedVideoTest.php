<?php

namespace BNETDocs\Tests\Libraries\Discord;

use \BNETDocs\Libraries\Discord\EmbedVideo;
use \PHPUnit\Framework\TestCase;

class EmbedVideoTest extends TestCase
{
    // Constructor

    public function testConstructorSetsUrl(): void
    {
        $v = new EmbedVideo('https://example.com/video.mp4');
        $data = $v->jsonSerialize();
        $this->assertSame('https://example.com/video.mp4', $data['url']);
    }

    public function testConstructorOptionalDimensions(): void
    {
        $v = new EmbedVideo('https://example.com/video.mp4', 1920, 1080);
        $data = $v->jsonSerialize();
        $this->assertSame(1920, $data['width']);
        $this->assertSame(1080, $data['height']);
    }

    // jsonSerialize — empty() strips zero integers and empty url

    public function testJsonSerializeOmitsZeroWidth(): void
    {
        $v = new EmbedVideo('https://example.com/video.mp4', 0, 0);
        $data = $v->jsonSerialize();
        $this->assertArrayNotHasKey('width', $data);
    }

    public function testJsonSerializeOmitsZeroHeight(): void
    {
        $v = new EmbedVideo('https://example.com/video.mp4', 0, 0);
        $data = $v->jsonSerialize();
        $this->assertArrayNotHasKey('height', $data);
    }

    public function testJsonSerializeOmitsEmptyUrl(): void
    {
        $v = new EmbedVideo('');
        $data = $v->jsonSerialize();
        $this->assertArrayNotHasKey('url', $data);
    }

    public function testJsonSerializeIncludesNonZeroDimensions(): void
    {
        $v = new EmbedVideo('https://example.com/video.mp4', 640, 360);
        $data = $v->jsonSerialize();
        $this->assertArrayHasKey('width', $data);
        $this->assertArrayHasKey('height', $data);
    }
}
