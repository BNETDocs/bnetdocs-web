<?php

namespace BNETDocs\Views\User;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\User\ResetPassword as UserResetPasswordModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class ResetPasswordHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof UserResetPasswordModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'User/ResetPassword'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
