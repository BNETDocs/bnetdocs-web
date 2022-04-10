<?php

namespace BNETDocs\Views\User;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\User\Update as UserUpdateModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class UpdateHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof UserUpdateModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'User/Update'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
