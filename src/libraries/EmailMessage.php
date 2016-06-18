<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\HTTPHeader;
use \BNETDocs\Libraries\Pair;
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

  public function build() {
    // This should be overridden by subclasses. It is only present here
    // because it is called upon elsewhere in the code.
    return false;
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
      $buffer .= (string) $obj;
    }
    return $buffer;
  }

  protected function setMultiPartBody(array &$bodies) {
    $boundary = "bnetdocs" . (mt_rand() * mt_rand());
    $this->setHeader(
      "Content-Type",
      "multipart/alternative;boundary=" . $boundary
    );
    $body = "\n";
    foreach ($bodies as $part) {
      // $part should be a Pair class.
      $body .= "--" . $boundary . "\n";
      $body .= "Content-Type: " . $part->getKey() . "\n\n";
      $body .= $part->getValue() . "\n";
    }
    return $this->setBody($body);
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
