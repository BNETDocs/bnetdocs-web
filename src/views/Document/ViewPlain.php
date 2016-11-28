<?php

namespace BNETDocs\Views\Document;

use \BNETDocs\Models\Document\View as DocumentViewModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class ViewPlain extends View {

  public function getMimeType() {
    return "text/plain;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof DocumentViewModel) {
      throw new IncorrectModelException();
    }
    echo $model->document->getTitle() . "\n";
    echo str_repeat("=", strlen($model->document->getTitle())) . "\n\n";
    echo $model->document->getContent(false);
  }

}
