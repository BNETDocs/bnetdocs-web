<?php

namespace BNETDocs\Views\Document;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Document\View as DocumentViewModel;

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
