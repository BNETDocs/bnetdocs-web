<?php

namespace BNETDocs\Views\User;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\User\Logout as UserLogoutModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class LogoutHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof UserLogoutModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'User/Logout'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
