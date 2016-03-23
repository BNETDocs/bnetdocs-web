<?php

namespace BNETDocs\Views\User;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\User\ChangePassword as UserChangePasswordModel;

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
