<?php

namespace BNETDocs\Views\Document;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Document\Sitemap as DocumentSitemapModel;

class SitemapJSON extends View {

  public function getMimeType() {
    return "application/json;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof DocumentSitemapModel) {
      throw new IncorrectModelException();
    }
    echo json_encode([
      "documents" => $model->documents
    ], Common::prettyJSONIfBrowser());
  }

}
