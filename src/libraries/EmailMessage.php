<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\HTTPHeader;
use \BNETDocs\Libraries\Pair;
use \SplObjectStorage;
use \UnexpectedValueException;

abstract class EmailMessage {

  protected $body;
  protected $headers;

  public function __construct() {
    $this->body    = "";
    $this->headers = new SplObjectStorage();
  }

  public function addHeader($name, $value) {
    $this->headers->attach(new HTTPHeader($name, $value));
  }

  public abstract function build();

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

  public function getHeaderFirst($name) {
    $value = $this->getHeader($name);
    $value->rewind(); // Set cursor to first item
    return ($value->valid() ? $value->current()->getValue() : null);
  }

  public function getHeaders() {
    $buffer = "";
    foreach ($this->headers as $obj) {
      $buffer .= (string) $obj;
    }
    return $buffer;
  }

  protected function setMultiPartBody(array &$bodies) {
    $boundary = "bnetdocs" . hash("sha1", mt_rand());
    $this->setHeader(
      "Content-Type",
      "multipart/alternative;boundary=" . $boundary
    );
    $body = "\n";
    foreach ($bodies as $part) {
      if (!$part instanceof Pair) {
        throw new UnexpectedValueException();
      }
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
