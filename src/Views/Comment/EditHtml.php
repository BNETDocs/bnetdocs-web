<?php

namespace BNETDocs\Views\Comment;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\Comment\Edit as CommentEditModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class EditHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof CommentEditModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Comment/Edit'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
