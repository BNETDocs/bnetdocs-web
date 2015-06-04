<?php

namespace BNETDocs\Views;

use BNETDocs\Libraries\Common;
use BNETDocs\Libraries\Exceptions\IncorrectModelException;
use BNETDocs\Libraries\Model;
use BNETDocs\Libraries\Template;
use BNETDocs\Libraries\View;
use BNETDocs\Models\Legal as LegalModel;

class LegalHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof LegalModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "Legal"))->render();
  }

}
