<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\RedirectSoft as RedirectSoftModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class RedirectSoft extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model               = new RedirectSoftModel();
    $model->location     = Common::relativeUrlToAbsolute(array_shift($args));
    $model->user_session = UserSession::load($router);

    $view->render($model);

    $model->_responseCode                    = 302;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseHeaders["Location"]     = $model->location;
    $model->_responseTTL                     = 0;

    return $model;

  }

}
