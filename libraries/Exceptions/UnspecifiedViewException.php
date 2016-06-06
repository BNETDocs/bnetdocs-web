<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \Exception;

class UnspecifiedViewException extends BNETDocsException {

  public function __construct(Exception $prev_ex = null) {
    parent::__construct(
      "Unspecified view provided to controller", 3, $prev_ex
    );
  }

}
