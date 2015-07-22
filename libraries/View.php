<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Model;

abstract class View {

  public abstract function getMimeType();
  public abstract function render(Model &$model);

}
