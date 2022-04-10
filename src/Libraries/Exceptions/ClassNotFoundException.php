<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \BNETDocs\Libraries\Logger;
use \Exception;

class ClassNotFoundException extends BNETDocsException {

  public function __construct($className, Exception &$prev_ex = null) {
    parent::__construct("Required class '$className' not found", 1, $prev_ex);
    Logger::logMetric("className", $className);
  }

}
