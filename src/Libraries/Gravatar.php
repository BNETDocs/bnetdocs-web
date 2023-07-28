<?php

namespace BNETDocs\Libraries;

class Gravatar implements \JsonSerializable
{
  public const GRAVATAR_BASE_URL = '//www.gravatar.com/avatar/';

  protected string $email;

  public function __construct(string $email)
  {
    $this->setEmail($email);
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function getHash(): string
  {
    return \hash('md5', \strtolower(\trim($this->email)));
  }

  public function getUrl(
    ?int $size = null, ?string $default = null, ?string $forcedefault = null, ?string $rating = null
  ): string
  {
    $url = self::GRAVATAR_BASE_URL . $this->getHash();
    $args = [];
    if (!is_null($size))         $args['s'] = $size;
    if (!is_null($default))      $args['d'] = $default;
    if (!is_null($forcedefault)) $args['f'] = $forcedefault;
    if (!is_null($rating))       $args['r'] = $rating;
    $query = \http_build_query($args);
    if ($query) $url .= '?' . $query;
    return $url;
  }

  public function jsonSerialize(): mixed
  {
    return [
      'email' => $this->getEmail(),
      'hash' => $this->getHash(),
      'url' => $this->getUrl(),
    ];
  }

  public function setEmail(string $value): void
  {
    if (!\filter_var($value, \FILTER_VALIDATE_EMAIL)) throw new \UnexpectedValueException();

    $this->email = $value;
  }
}
