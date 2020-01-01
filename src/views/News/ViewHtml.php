<?php

namespace BNETDocs\Views\News;

use \BNETDocs\Models\News\View as NewsViewModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class ViewHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof NewsViewModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'News/View'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
