<?php

namespace BNETDocs\Views\User;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\User\Register as UserRegisterModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class RegisterHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof UserRegisterModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'User/Register'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
