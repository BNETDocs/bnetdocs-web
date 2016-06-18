<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Logger;

abstract class Model {

  public function __construct() {
    Logger::logMetric("model", get_class($this));
  }

}
