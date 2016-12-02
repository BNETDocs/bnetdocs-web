<?php

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Packet\Popular as PacketPopularModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Popular extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new PacketPopularModel();
    $model->user_session = UserSession::load($router);

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
