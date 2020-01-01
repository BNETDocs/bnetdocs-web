<?php

namespace BNETDocs\Views\Document;

use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;
use \BNETDocs\Models\Document\Edit as DocumentEditModel;

class EditHtml extends View {
  public function getMimeType() {
    return 'text/html;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof DocumentEditModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, 'Document/Edit'))->render();
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
