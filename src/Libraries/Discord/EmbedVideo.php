<?php

namespace BNETDocs\Libraries\Discord;

// <https://discordapp.com/developers/docs/resources/channel#embed-object-embed-video-structure>

class EmbedVideo implements \JsonSerializable
{
  protected int $height;
  protected string $url;
  protected int $width;

  public function __construct(string $url, int $width = 0, int $height = 0)
  {
    $this->setHeight($height);
    $this->setUrl($url);
    $this->setWidth($width);
  }

  public function jsonSerialize() : mixed
  {
    $r = [
      'height' => $this->height,
      'url' => $this->url,
      'width' => $this->width,
    ];
    foreach ($r as $k => $v) if (empty($v)) unset($r[$k]);
    return $r;
  }

  public function setHeight(int $height) : void
  {
    $this->height = $height;
  }

  public function setUrl(string $url) : void
  {
    $this->url = $url;
  }

  public function setWidth(int $width) : void
  {
    $this->width = $width;
  }
}
