<?php

namespace BNETDocs\Views;

use BNETDocs\Libraries\Common;
use BNETDocs\Libraries\Exceptions\IncorrectModelException;
use BNETDocs\Libraries\Model;
use BNETDocs\Libraries\Template;
use BNETDocs\Libraries\View;
use BNETDocs\Models\Credits as CreditsModel;

class CreditsHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof CreditsModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "Credits"))->render();
  }

}
