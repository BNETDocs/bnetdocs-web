<?php

namespace BNETDocs\Views\User;

use \BNETDocs\Models\User\ChangePassword as UserChangePasswordModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class ChangePasswordHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof UserChangePasswordModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "User/ChangePassword"))->render();
  }

}
