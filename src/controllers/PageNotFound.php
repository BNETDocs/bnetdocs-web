<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\PageNotFound as PageNotFoundModel;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class PageNotFound extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new PageNotFoundModel();
    $model->user_session = UserSession::load($router);

    $view->render($model);

    $model->_responseCode = 404;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
