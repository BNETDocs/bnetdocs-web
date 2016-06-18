<?php

namespace BNETDocs\Views\News;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\News\Edit as NewsEditModel;

class EditHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof NewsEditModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "News/Edit"))->render();
  }

}
