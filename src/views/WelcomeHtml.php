<?php

namespace BNETDocs\Views;

use \BNETDocs\Models\Welcome as WelcomeModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class WelcomeHtml extends View {

  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof WelcomeModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Welcome'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }

}
