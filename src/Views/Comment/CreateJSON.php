<?php

namespace BNETDocs\Views\Comment;

use \BNETDocs\Models\Comment\Create as CreateModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class CreateJSON extends View {
  public function getMimeType() {
    return 'application/json;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof CreateModel) {
      throw new IncorrectModelException();
    }
    echo json_encode($model->response, Common::prettyJSONIfBrowser());
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
