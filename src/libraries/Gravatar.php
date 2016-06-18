<?php

namespace BNETDocs\Libraries;

class Gravatar {

  const GRAVATAR_BASE_URL = "//www.gravatar.com/avatar/";

  protected $email;

  public function __construct($email) {
    $this->email = $email;
  }

  public function getEmail() {
    return $this->email;
  }

  public function getHash() {
    return hash("md5", strtolower(trim($this->email)));
  }

  public function getUrl(
    $size = null, $default = null, $forcedefault = null, $rating = null
  ) {
    $url = self::GRAVATAR_BASE_URL . $this->getHash();
    $args = [];
    if (!is_null($size))         $args["s"] = $size;
    if (!is_null($default))      $args["d"] = $default;
    if (!is_null($forcedefault)) $args["f"] = $forcedefault;
    if (!is_null($rating))       $args["r"] = $rating;
    $query = http_build_query($args);
    if ($query) $url .= "?" . $query;
    return $url;
  }
}
