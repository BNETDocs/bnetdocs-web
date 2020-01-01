<?php

namespace BNETDocs\Views;

use \BNETDocs\Models\Legal as LegalModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class LegalPlain extends View {

  public function getMimeType() {
    return 'text/plain;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof LegalModel) {
      throw new IncorrectModelException();
    }
    echo $model->license;
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }

}
