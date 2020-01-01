<?php

namespace BNETDocs\Views\Document;

use \BNETDocs\Models\Document\Index as DocumentIndexModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\View;

class IndexJSON extends View {
  public function getMimeType() {
    return 'application/json;charset=utf-8';
  }

  public function render(Model &$model) {
    if (!$model instanceof DocumentIndexModel) {
      throw new IncorrectModelException();
    }
    echo json_encode([
      'documents' => $model->documents
    ], Common::prettyJSONIfBrowser());
    $model->_responseHeaders['Content-Type'] = $this->getMimeType();
  }
}
