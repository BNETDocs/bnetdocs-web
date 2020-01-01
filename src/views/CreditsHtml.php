<?php

namespace BNETDocs\Views;

use \BNETDocs\Models\Credits as CreditsModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class CreditsHtml extends View {

  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof CreditsModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Credits'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }

}
