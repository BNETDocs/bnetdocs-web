<?php

namespace BNETDocs\Views;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\PageNotFound as PageNotFoundModel;

class PageNotFoundJSON extends View {

  public function getMimeType() {
    return "application/json;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof PageNotFoundModel) {
      throw new IncorrectModelException();
    }
    echo json_encode("Object Not Found\n"
      . "The requested resource does not exist or could not be found.\n"
    , Common::prettyJSONIfBrowser());
  }

}
