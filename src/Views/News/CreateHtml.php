<?php

namespace BNETDocs\Views\News;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\News\Create as NewsCreateModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class CreateHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof NewsCreateModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'News/Create'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
