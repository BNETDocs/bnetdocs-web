<?php

namespace BNETDocs\Views\Document;

use \BNETDocs\Libraries\Template;
use \BNETDocs\Models\Document\View as DocumentViewModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class ViewHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof DocumentViewModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Document/View'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
