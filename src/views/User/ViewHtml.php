<?php

namespace BNETDocs\Views\User;

use \BNETDocs\Models\User\View as UserViewModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class ViewHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof UserViewModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'User/View'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
