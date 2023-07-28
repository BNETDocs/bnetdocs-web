<?php

namespace BNETDocs\Libraries\Discord;

// <https://discordapp.com/developers/docs/resources/channel#embed-object-embed-footer-structure>

class EmbedFooter implements \JsonSerializable
{
  public const MAX_TEXT = 2048;

  protected string $icon_url;
  protected string $proxy_icon_url;
  protected string $text;

  public function __construct(string $text, string $icon_url = '', string $proxy_icon_url = '')
  {
    $this->setIconUrl($icon_url);
    $this->setProxyIconUrl($proxy_icon_url);
    $this->setText($text);
  }

  public function jsonSerialize() : mixed
  {
    $r = [
      'icon_url' => $this->icon_url,
      'proxy_icon_url' => $this->proxy_icon_url,
      'text' => $this->text,
    ];
    foreach ($r as $k => $v) if (empty($v)) unset($r[$k]);
    return $r;
  }

  public function setIconUrl(string $value): void
  {
    $this->icon_url = $value;
  }

  public function setProxyIconUrl(string $value): void
  {
    $this->proxy_icon_url = $value;
  }

  public function setText(string $value): void
  {
    if (strlen($value) > self::MAX_TEXT)
    {
      throw new \LengthException(sprintf(
        'Discord forbids text longer than %d characters', self::MAX_TEXT
      ));
    }

    $this->text = $value;
  }
}
