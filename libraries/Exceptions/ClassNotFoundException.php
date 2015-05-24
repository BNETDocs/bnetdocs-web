<?php

namespace BNETDocs\Libraries\Exceptions;

use BNETDocs\Libraries\Exceptions\BNETDocsException;

class ClassNotFoundException extends BNETDocsException {

  public function __construct($className, \Exception &$prev_ex = null) {
    parent::__construct("Required class '$className' not found", 2, $prev_ex);
  }

}
