<?php

namespace BNETDocs\Libraries\Exceptions;

use BNETDocs\Libraries\Exceptions\BNETDocsException;

class ServiceUnavailableException extends BNETDocsException {

  public function __construct(\Exception &$prev_ex = null) {
    parent::__construct("BNETDocs service is currently offline", 1, $prev_ex);
    $this->httpResponseCode = 503;
  }

}
