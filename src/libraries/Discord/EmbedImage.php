<?php

namespace BNETDocs\Libraries\Discord;

use \JsonSerializable;

// <https://discordapp.com/developers/docs/resources/channel#embed-object-embed-image-structure>

class EmbedImage implements JsonSerializable {

  protected $height;
  protected $proxy_url;
  protected $url;
  protected $width;

  public function __construct(string $url, int $width = 0, int $height = 0, string $proxy_url = '') {
    $this->setHeight($height);
    $this->setProxyUrl($proxy_url);
    $this->setUrl($url);
    $this->setWidth($width);
  }

  public function jsonSerialize() {
    // part of JsonSerializable interface
    $r = array();

    if (!empty($this->proxy_url)) $r['proxy_url'] = $this->proxy_url;
    if (!empty($this->url)) $r['url'] = $this->url;
    if ($this->height != 0) $r['height'] = $this->height;
    if ($this->width != 0) $r['width'] = $this->width;

    return $r;
  }

  public function setHeight(int $height) {
    $this->height = $height;
  }

  public function setProxyUrl(string $proxy_url) {
    $this->proxy_url = $proxy_url;
  }

  public function setUrl(string $url) {
    $this->url = $url;
  }

  public function setWidth(int $width) {
    $this->width = $width;
  }

}
