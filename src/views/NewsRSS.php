<?php

namespace BNETDocs\Views;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\News as NewsModel;

class NewsRSS extends View {

  public function getMimeType() {
    return "application/rss+xml;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof NewsModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "News.rss"))->render();
  }

}
