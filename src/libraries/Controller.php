<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Router;

abstract class Controller {

  public function __construct() {
    Logger::logMetric("controller", get_class($this));
  }

  public abstract function run(Router &$router);

}
