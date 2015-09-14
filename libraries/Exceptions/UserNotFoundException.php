<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \BNETDocs\Libraries\Logger;
use \Exception;

class UserNotFoundException extends BNETDocsException {

  public function __construct($query, Exception &$prev_ex = null) {
    parent::__construct("User not found", 7, $prev_ex);
    Logger::logMetric("query", $query);
  }

}
