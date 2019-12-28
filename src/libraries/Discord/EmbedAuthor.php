<?php

namespace BNETDocs\Libraries\Discord;

use \JsonSerializable;
use \LengthException;

// <https://discordapp.com/developers/docs/resources/channel#embed-object-embed-author-structure>

class EmbedAuthor implements JsonSerializable {

  const MAX_NAME = 256;

  protected $icon_url;
  protected $name;
  protected $proxy_icon_url;
  protected $url;

  public function __construct(string $name, string $url = '', string $icon_url = '') {
    $this->setIconUrl($icon_url);
    $this->setName($name);
    $this->setProxyIconUrl('');
    $this->setUrl($url);
  }

  public function jsonSerialize() {
    // part of JsonSerializable interface
    $r = array();

    if (!empty($this->icon_url)) $r['icon_url'] = $this->icon_url;
    if (!empty($this->name)) $r['name'] = $this->name;
    if (!empty($this->proxy_icon_url)) $r['proxy_icon_url'] = $this->proxy_icon_url;
    if (!empty($this->url)) $r['url'] = $this->url;

    return $r;
  }

  public function setIconUrl(string $icon_url) {
    $this->icon_url = $icon_url;
  }

  public function setName(string $name) {
    if (empty($name)) {
      throw new LengthException('The name cannot be empty');
    }

    if (strlen($name) > self::MAX_NAME) {
      throw new LengthException(sprintf(
        'Discord forbids name longer than %d characters', self::MAX_NAME
      ));
    }

    $this->name = $name;
  }

  public function setProxyIconUrl(string $proxy_icon_url) {
    $this->proxy_icon_url = $proxy_icon_url;
  }

  public function setUrl(string $url) {
    $this->url = $url;
  }

}
