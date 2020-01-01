<?php

namespace BNETDocs\Views;

use \BNETDocs\Models\Legal as LegalModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class LegalHtml extends View {

  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof LegalModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Legal'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }

}
