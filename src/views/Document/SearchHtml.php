<?php

namespace BNETDocs\Views\Document;

use \BNETDocs\Models\Document\Search as DocumentSearchModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class SearchHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof DocumentSearchModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "Document/Search"))->render();
  }

}
