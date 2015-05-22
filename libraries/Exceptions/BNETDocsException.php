<?php

namespace BNETDocs\Libraries\Exceptions;

abstract class BNETDocsException extends \Exception {

  protected $httpResponseCode  = 500;
  private $httpResponseHeaders = [];

  protected function clearHTTPResponseHeaders() {
    unset($this->httpResponseHeaders);
    $this->httpResponseHeaders = [];
  }

  public function getHTTPResponseCode() {
    return $this->httpResponseCode;
  }

  public function getHTTPResponseHeaders() {
    return $this->httpResponseHeaders;
  }

  protected function setHTTPResponseHeader($name, $value) {
    $this->httpResponseHeaders[$name] = $value;
  }

}
