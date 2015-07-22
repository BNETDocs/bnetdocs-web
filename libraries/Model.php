<?php

namespace BNETDocs\Libraries;

abstract class Model {

  public function __construct() {
    Logger::logMetric("model", get_class($this));
  }

}
