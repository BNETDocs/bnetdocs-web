<?php

namespace BNETDocs\Views;

use \BNETDocs\Models\News as NewsModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class NewsRSS extends View {

  public function getMimeType() {
    return 'application/rss+xml;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof NewsModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'News.rss'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }

}
