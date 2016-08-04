<?php

namespace BNETDocs\Views\Comment;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Comment\Delete as CommentDeleteModel;

class DeleteHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof CommentDeleteModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "Comment/Delete"))->render();
  }

}
