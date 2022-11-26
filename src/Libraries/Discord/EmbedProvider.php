<?php

namespace BNETDocs\Libraries\Discord;

// <https://discordapp.com/developers/docs/resources/channel#embed-object-embed-provider-structure>

class EmbedProvider implements \JsonSerializable
{
  protected string $name;
  protected string $url;

  public function __construct(string $name, string $url = '')
  {
    $this->setName($name);
    $this->setUrl($url);
  }

  public function jsonSerialize() : mixed
  {
    $r = [
      'name' => $this->name,
      'url' => $this->url,
    ];
    foreach ($r as $k => $v) if (empty($v)) unset($r[$k]);
    return $r;
  }

  public function setName(string $value) : void
  {
    $this->name = $value;
  }

  public function setUrl(string $value) : void
  {
    $this->url = $value;
  }
}
