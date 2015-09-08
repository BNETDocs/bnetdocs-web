<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Model;

abstract class View {

  public function __construct() {
    Logger::logMetric("view", get_class($this));
  }

  public abstract function getMimeType();
  public abstract function render(Model &$model);

}
