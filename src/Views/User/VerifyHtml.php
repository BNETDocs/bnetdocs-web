<?php

namespace BNETDocs\Views\User;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\User\Verify as UserVerifyModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class VerifyHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof UserVerifyModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'User/Verify'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
