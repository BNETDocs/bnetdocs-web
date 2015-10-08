<?php

namespace BNETDocs\Views;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\PageNotFound as PageNotFoundModel;

class PageNotFoundPlain extends View {

  public function getMimeType() {
    return "text/plain;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof PageNotFoundModel) {
      throw new IncorrectModelException();
    }
    echo "Document Not Found\n"
      . "The requested resource does not exist or could not be found.\n";
  }

}
