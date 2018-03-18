<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Models\User\ResetPassword as UserResetPasswordModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class ResetPassword extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new UserResetPasswordModel();

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
