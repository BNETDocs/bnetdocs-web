<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \Exception;

class DatabaseUnavailableException extends BNETDocsException {

  public function __construct(Exception &$prev_ex = null) {
    parent::__construct(
      "All configured databases are unavailable", 6, $prev_ex
    );
  }

}
