<?php

namespace BNETDocs\Views\Server;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\Server\View as ServerViewModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class ViewHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof ServerViewModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Server/View'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
