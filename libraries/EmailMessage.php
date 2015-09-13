<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\HTTPHeader;
use \SplObjectStorage;

class EmailMessage {

  protected $body;
  protected $headers;

  public function __construct() {
    $this->body    = "";
    $this->headers = new SplObjectStorage();
  }

  public function addHeader($name, $value) {
    $this->headers->attach(new HTTPHeader($name, $value));
  }

  public function getBody() {
    return $this->body;
  }

  public function getHeader($name) {
    $objs = new SplObjectStorage();
    foreach ($this->headers as $obj) {
      if (strtolower($obj->getName()) == strtolower($name)) {
        $objs->attach($obj);
      }
    }
    return $objs;
  }

  public function getHeaders() {
    $buffer = "";
    foreach ($this->headers as $obj) {
      $buffer .= (string)$obj;
    }
    return $buffer;
  }

  public function setBody($body) {
    $this->body = $body;
  }

  public function setHeader($name, $value) {
    foreach ($this->headers as $obj) {
      if (strtolower($obj->getKey()) == strtolower($name)) {
        $this->headers->detach($obj);
        break;
      }
    }
    return $this->addHeader($name, $value);
  }

}
