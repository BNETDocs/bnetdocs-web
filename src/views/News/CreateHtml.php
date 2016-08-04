<?php

namespace BNETDocs\Views\News;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\News\Create as NewsCreateModel;

class CreateHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof NewsCreateModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "News/Create"))->render();
  }

}
