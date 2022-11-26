<?php

namespace BNETDocs\Libraries\Discord;

// <https://discordapp.com/developers/docs/resources/channel#embed-object-embed-image-structure>

class EmbedImage implements \JsonSerializable
{
  protected int $height;
  protected string $proxy_url;
  protected string $url;
  protected int $width;

  public function __construct(string $url, int $width = 0, int $height = 0, string $proxy_url = '')
  {
    $this->setHeight($height);
    $this->setProxyUrl($proxy_url);
    $this->setUrl($url);
    $this->setWidth($width);
  }

  public function jsonSerialize() : mixed
  {
    $r = [
      'height' => $this->height,
      'proxy_url' => $this->proxy_url,
      'url' => $this->url,
      'width' => $this->width,
    ];
    foreach ($r as $k => $v) if (empty($v)) unset($r[$k]);
    return $r;
  }

  public function setHeight(int $value) : void
  {
    $this->height = $value;
  }

  public function setProxyUrl(string $value) : void
  {
    $this->proxy_url = $value;
  }

  public function setUrl(string $value) : void
  {
    $this->url = $value;
  }

  public function setWidth(int $value) : void
  {
    $this->width = $value;
  }
}
