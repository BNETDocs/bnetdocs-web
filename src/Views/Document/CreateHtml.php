<?php

namespace BNETDocs\Views\Document;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\Document\Create as DocumentCreateModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class CreateHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof DocumentCreateModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Document/Create'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
