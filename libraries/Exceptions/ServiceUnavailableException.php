<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \Exception;

class ServiceUnavailableException extends BNETDocsException {

  public function __construct(Exception &$prev_ex = null) {
    parent::__construct("BNETDocs is currently offline", 1, $prev_ex);
    $this->httpResponseCode = 503;
  }

}
