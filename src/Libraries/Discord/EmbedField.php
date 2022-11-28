<?php

namespace BNETDocs\Libraries\Discord;

use \LengthException;
use \UnexpectedValueException;

// <https://discordapp.com/developers/docs/resources/channel#embed-object-embed-field-structure>

class EmbedField implements \JsonSerializable
{
  public const MAX_NAME = 256;
  public const MAX_VALUE = 1024;

  protected bool $inline;
  protected string $name;
  protected string $value;

  public function __construct(string $name, $value, bool $inline)
  {
    $this->setInline($inline);
    $this->setName($name);
    $this->setValue($value);
  }

  public function jsonSerialize() : mixed
  {
    $r = [
      'name' => $this->name,
      'value' => $this->value,
      'inline' => $this->inline,
    ];
    foreach ($r as $k => $v) if (empty($v)) unset($r[$k]);
    return $r;
  }

  public function setInline(bool $inline) : void
  {
    $this->inline = $inline;
  }

  public function setName(string $name) : void
  {
    if (strlen($name) > self::MAX_NAME)
      throw new LengthException(sprintf(
        'Discord forbids name longer than %d characters', self::MAX_NAME
      ));

    $this->name = $name;
  }

  public function setValue(int|float|string|bool $value) : void
  {
    if (is_string($value) && strlen($value) > self::MAX_VALUE)
      throw new LengthException(sprintf(
        'Discord forbids value longer than %d characters', self::MAX_VALUE
      ));

    $this->value = $value;
  }
}
