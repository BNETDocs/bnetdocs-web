<?php

namespace BNETDocs\Views\User;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\User\View as UserViewModel;

class ViewHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof UserViewModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "User/View"))->render();
  }

}
