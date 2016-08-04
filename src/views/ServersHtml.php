<?php

namespace BNETDocs\Views;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Servers as ServersModel;

class ServersHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof ServersModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "Servers"))->render();
  }

}
