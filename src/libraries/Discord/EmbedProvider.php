<?php

namespace BNETDocs\Libraries\Discord;

use \JsonSerializable;

// <https://discordapp.com/developers/docs/resources/channel#embed-object-embed-provider-structure>

class EmbedProvider implements JsonSerializable {

  protected $name;
  protected $url;

  public function __construct(string $name, string $url = '') {
    $this->setName($name);
    $this->setUrl($url);
  }

  public function jsonSerialize() {
    // part of JsonSerializable interface
    $r = array();

    if (!empty($this->name)) $r['name'] = $this->name;
    if (!empty($this->url)) $r['url'] = $this->url;

    return $r;
  }

  public function setName(string $name) {
    $this->name = $name;
  }

  public function setUrl(string $url) {
    $this->url = $url;
  }

}
