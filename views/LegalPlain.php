<?php

namespace BNETDocs\Views;

use BNETDocs\Libraries\Common;
use BNETDocs\Libraries\Exceptions\IncorrectModelException;
use BNETDocs\Libraries\Model;
use BNETDocs\Libraries\View;
use BNETDocs\Models\Legal as LegalModel;

class LegalPlain extends View {

  public function getMimeType() {
    return "text/plain;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof LegalModel) {
      throw new IncorrectModelException();
    }
    echo file_get_contents("./LICENSE");
  }

}
