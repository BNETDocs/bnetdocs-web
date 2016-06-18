<?php

namespace BNETDocs\Libraries;

use \Serializable;

class Pair implements Serializable {

  public function __construct($key, $value) {
    $this->key   = $key;
    $this->value = $value;
  }

  public function getKey() {
    return $this->key;
  }

  public function getValue() {
    return $this->value;
  }

  public function serialize() {
    return serialize([$this->key, $this->value]);
  }

  public function unserialize($data) {
    $this->key   = $data[0];
    $this->value = $data[1];
  }

}
