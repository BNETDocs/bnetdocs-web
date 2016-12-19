<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Models\Legal as LegalModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Legal extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new LegalModel();
    $model->license = file_get_contents("../LICENSE.txt");

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
