<?php

namespace BNETDocs\Views;

use \BNETDocs\Models\FrontPage as FrontPageModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class FrontPageHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof FrontPageModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "FrontPage"))->render();
  }

}
