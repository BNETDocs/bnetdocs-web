<?php

namespace BNETDocs\Views\User;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\User\Login as UserLoginModel;

class LoginHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof UserLoginModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "User/Login"))->render();
  }

}
