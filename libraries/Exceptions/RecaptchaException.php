<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \Exception;

class RecaptchaException extends BNETDocsException {

  public function __construct($message, Exception &$prev_ex = null) {
    parent::__construct($message, 16, $prev_ex);
  }

}
