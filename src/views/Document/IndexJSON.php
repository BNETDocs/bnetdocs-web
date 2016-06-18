<?php

namespace BNETDocs\Views\Document;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Document\Index as DocumentIndexModel;

class IndexJSON extends View {

  public function getMimeType() {
    return "application/json;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof DocumentIndexModel) {
      throw new IncorrectModelException();
    }
    echo json_encode([
      "documents" => $model->documents
    ], Common::prettyJSONIfBrowser());
  }

}
