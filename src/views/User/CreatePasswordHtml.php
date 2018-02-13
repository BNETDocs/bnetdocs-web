<?php

namespace BNETDocs\Views\User;

use \BNETDocs\Models\User\CreatePassword as UserCreatePasswordModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class CreatePasswordHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof UserCreatePasswordModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "User/CreatePassword"))->render();
  }

}
