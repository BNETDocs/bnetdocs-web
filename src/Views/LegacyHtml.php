<?php

namespace BNETDocs\Views;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\Legacy as LegacyModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class LegacyHtml extends View {

  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof LegacyModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Legacy'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }

}
