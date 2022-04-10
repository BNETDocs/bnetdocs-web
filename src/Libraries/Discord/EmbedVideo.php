<?php

namespace BNETDocs\Libraries\Discord;

use \JsonSerializable;

// <https://discordapp.com/developers/docs/resources/channel#embed-object-embed-video-structure>

class EmbedVideo implements JsonSerializable {

  protected $height;
  protected $url;
  protected $width;

  public function __construct(string $url, int $width = 0, int $height = 0) {
    $this->setHeight($height);
    $this->setUrl($url);
    $this->setWidth($width);
  }

  public function jsonSerialize() {
    // part of JsonSerializable interface
    $r = array();

    if (!empty($this->url)) $r['url'] = $this->url;
    if ($this->height != 0) $r['height'] = $this->height;
    if ($this->width != 0) $r['width'] = $this->width;

    return $r;
  }

  public function setHeight(int $height) {
    $this->height = $height;
  }

  public function setUrl(string $url) {
    $this->url = $url;
  }

  public function setWidth(int $width) {
    $this->width = $width;
  }

}
