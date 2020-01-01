<?php

namespace BNETDocs\Views\News;

use \BNETDocs\Models\News\Edit as NewsEditModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class EditHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof NewsEditModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'News/Edit'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
