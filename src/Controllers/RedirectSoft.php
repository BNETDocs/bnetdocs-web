<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Models\RedirectSoft as RedirectSoftModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class RedirectSoft extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model           = new RedirectSoftModel();
    $model->location = Common::relativeUrlToAbsolute(array_shift($args));

    $view->render($model);
    $model->_responseCode = 302;
    $model->_responseHeaders['Location'] = $model->location;
    return $model;
  }
}
