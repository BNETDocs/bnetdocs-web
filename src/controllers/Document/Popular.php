<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Models\Document\Popular as DocumentPopularModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Popular extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new DocumentPopularModel();

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
