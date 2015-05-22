<?php

namespace BNETDocs\Libraries\Exceptions;

use BNETDocs\Libraries\Exceptions\BNETDocsException;

class ControllerNotFoundException extends BNETDocsException {

  public function __construct($controllerName, $prev_ex = null) {
    parent::__construct("Unable to find a suitable controller given the path", 3, $prev_ex);
    $this->httpResponseCode = 404;
  }

}
