<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \Exception;

class IncorrectModelException extends BNETDocsException {

  public function __construct(Exception $prev_ex = null) {
    parent::__construct("Incorrect model provided to view", 3, $prev_ex);
  }

}
