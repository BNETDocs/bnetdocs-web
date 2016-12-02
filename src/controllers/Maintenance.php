<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Models\Maintenance as MaintenanceModel;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Maintenance extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new MaintenanceModel();
    $model->message = array_shift($args);

    $view->render($model);

    $model->_responseCode = 503;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
