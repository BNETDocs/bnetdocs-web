<?php

namespace BNETDocs\Views;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\News as NewsModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class NewsHtml extends View {

  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof NewsModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'News'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }

}
