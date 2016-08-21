<?php

namespace BNETDocs\Views\Server;

use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Server\View as ServerViewModel;
use \CarlBennett\MVC\Libraries\Common;

class ViewHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof ServerViewModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "Server/View"))->render();
  }

}
