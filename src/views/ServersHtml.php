<?php

namespace BNETDocs\Views;

use \BNETDocs\Models\Servers as ServersModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

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
