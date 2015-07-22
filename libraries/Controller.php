<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Router;

abstract class Controller {

  public abstract function run(Router &$router);

}
