<?php

namespace BNETDocs\Libraries\Exceptions;

use \BNETDocs\Libraries\Exceptions\BNETDocsException;
use \BNETDocs\Libraries\Logger;
use \Exception;

class ControllerNotFoundException extends BNETDocsException {

  public function __construct($controllerName, Exception &$prev_ex = null) {
    parent::__construct("Unable to find a suitable controller given the path", 2, $prev_ex);
    Logger::logMetric("controllerName", $controllerName);
    $this->httpResponseCode = 404;
  }

}
