<?php

namespace BNETDocs\Libraries\Discord;

use \JsonSerializable;
use \LengthException;

// <https://discordapp.com/developers/docs/resources/channel#embed-object-embed-field-structure>

class EmbedField implements JsonSerializable {

  const MAX_NAME  = 256;
  const MAX_VALUE = 1024;

  protected $inline;
  protected $name;
  protected $value;

  public function __construct(string $name, $value, bool $inline) {
    $this->setInline($inline);
    $this->setName($name);
    $this->setValue($value);
  }

  public function jsonSerialize() {
    // part of JsonSerializable interface
    $r = array(
      'name' => $this->name,
      'value' => $this->value,
      'inline' => $this->inline,
    );

    return $r;
  }

  public function setInline(bool $inline) {
    $this->inline = $inline;
  }

  public function setName(string $name) {
    if (strlen($name) > self::MAX_NAME) {
      throw new LengthException(sprintf(
        'Discord forbids name longer than %d characters', self::MAX_NAME
      ));
    }

    $this->name = $name;
  }

  public function setValue($value) {
    if (!is_scalar($value)) {
      throw new UnexpectedValueException(
        'Expected integer, float, string, or boolean (scalar type) for value'
      );
    }

    if (strlen($value) > self::MAX_VALUE) {
      throw new LengthException(sprintf(
        'Discord forbids value longer than %d characters', self::MAX_VALUE
      ));
    }

    $this->value = $value;
  }

}
