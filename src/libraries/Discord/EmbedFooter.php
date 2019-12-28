<?php

namespace BNETDocs\Libraries\Discord;

use \JsonSerializable;

// <https://discordapp.com/developers/docs/resources/channel#embed-object-embed-footer-structure>

class EmbedFooter implements JsonSerializable {

  const MAX_TEXT = 2048;

  protected $icon_url;
  protected $proxy_icon_url;
  protected $text;

  public function __construct(string $text, string $icon_url = '', string $proxy_icon_url = '') {
    $this->setIconUrl($icon_url);
    $this->setProxyIconUrl($proxy_icon_url);
    $this->setText($text);
  }

  public function jsonSerialize() {
    // part of JsonSerializable interface
    $r = array();

    if (!empty($this->icon_url)) $r['icon_url'] = $this->icon_url;
    if (!empty($this->proxy_icon_url)) $r['proxy_icon_url'] = $this->proxy_icon_url;
    if (!empty($this->text)) $r['text'] = $this->text;

    return $r;
  }

  public function setIconUrl(string $icon_url) {
    $this->icon_url = $icon_url;
  }

  public function setProxyIconUrl(string $proxy_icon_url) {
    $this->proxy_icon_url = $proxy_icon_url;
  }

  public function setText(string $text) {
    if (strlen($text) > self::MAX_TEXT) {
      throw new LengthException(sprintf(
        'Discord forbids text longer than %d characters', self::MAX_TEXT
      ));
    }

    $this->text = $text;
  }

}
