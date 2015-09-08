<?php

namespace CarlBennett\API\Libraries\Exceptions;

use \CarlBennett\API\Libraries\Exceptions\APIException;
use \Exception;

class IncorrectModelException extends APIException {

  public function __construct(Exception $prev_ex = null) {
    parent::__construct("Incorrect model provided to view", 3, $prev_ex);
  }

}
