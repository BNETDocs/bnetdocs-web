<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\Update as UserUpdateModel;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Update extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UserUpdateModel();

    $data = $router->getRequestBodyArray();

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;
  }

}
