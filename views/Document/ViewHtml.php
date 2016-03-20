<?php

namespace BNETDocs\Views\Document;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Document\View as DocumentViewModel;

class ViewHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof DocumentViewModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "Document/View"))->render();
  }

}
