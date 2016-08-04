<?php

namespace BNETDocs\Views\Comment;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Comment\Create as CreateModel;

class CreateJSON extends View {

  public function getMimeType() {
    return "application/json;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof CreateModel) {
      throw new IncorrectModelException();
    }
    echo json_encode($model->response, Common::prettyJSONIfBrowser());
  }

}
