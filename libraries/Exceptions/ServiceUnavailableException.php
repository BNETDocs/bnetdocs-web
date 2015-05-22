<?php

namespace BNETDocs\Libraries\Exceptions;

use BNETDocs\Libraries\Exceptions\BNETDocsException;

class ServiceUnavailableException extends BNETDocsException {

  public function __construct($prev_ex = null) {
    parent::__construct("API service has been disabled", 1, $prev_ex);
    $this->httpResponseCode = 503;
  }

}
