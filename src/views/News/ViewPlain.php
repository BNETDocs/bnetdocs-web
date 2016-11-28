<?php

namespace BNETDocs\Views\News;

use \BNETDocs\Models\News\View as NewsViewModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

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
