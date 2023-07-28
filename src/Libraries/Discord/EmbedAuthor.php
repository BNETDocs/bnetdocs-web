<?php

namespace BNETDocs\Libraries\Discord;

// <https://discordapp.com/developers/docs/resources/channel#embed-object-embed-author-structure>

class EmbedAuthor implements \JsonSerializable
{
  public const MAX_NAME = 256;

  protected string $icon_url;
  protected string $name;
  protected string $proxy_icon_url;
  protected string $url;

  public function __construct(string $name, string $url = '', string $icon_url = '')
  {
    $this->setIconUrl($icon_url);
    $this->setName($name);
    $this->setProxyIconUrl('');
    $this->setUrl($url);
  }

  public function jsonSerialize(): mixed
  {
    $r = [
      'icon_url' => $this->icon_url,
      'name' => $this->name,
      'proxy_icon_url' => $this->proxy_icon_url,
      'url' => $this->url,
    ];
    foreach ($r as $k => $v) if (empty($v)) unset($r[$k]);
    return $r;
  }

  public function setIconUrl(string $value): void
  {
    $this->icon_url = $value;
  }

  public function setName(string $value): void
  {
    if (empty($value) || strlen($value) > self::MAX_NAME)
    {
      throw new \LengthException(sprintf(
        'Discord forbids name shorter than 1 or longer than %d characters', self::MAX_NAME
      ));
    }

    $this->name = $value;
  }

  public function setProxyIconUrl(string $value): void
  {
    $this->proxy_icon_url = $value;
  }

  public function setUrl(string $value): void
  {
    $this->url = $value;
  }
}
