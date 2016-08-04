<?php

namespace BNETDocs\Views\News;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\News\View as NewsViewModel;

class ViewPlain extends View {

  public function getMimeType() {
    return "text/plain;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof NewsViewModel) {
      throw new IncorrectModelException();
    }
    echo $model->news_post->getTitle() . "\n";
    echo str_repeat("=", strlen($model->news_post->getTitle())) . "\n\n";
    echo $model->news_post->getContent(false);
  }

}
