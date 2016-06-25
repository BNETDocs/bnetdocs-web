<?php

namespace BNETDocs\Views\API;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\API\Comment as CommentModel;
use \ReflectionExtension;

class CommentJSON extends View {

  public function getMimeType() {
    return "application/json;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof CommentModel) {
      throw new IncorrectModelException();
    }
    echo json_encode($model->response, Common::prettyJSONIfBrowser());
  }

}
