<?php

namespace BNETDocs\Views\News;

use \BNETDocs\Models\News\Delete as NewsDeleteModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class DeleteHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof NewsDeleteModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'News/Delete'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
