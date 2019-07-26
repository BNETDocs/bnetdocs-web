<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Models\Welcome as WelcomeModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Welcome extends Controller {

  public function &run( Router &$router, View &$view, array &$args ) {

    $model = new WelcomeModel();

    $view->render( $model );

    $model->_responseCode = 200;
    $model->_responseHeaders['Content-Type'] = $view->getMimeType();
    $model->_responseTTL = 300;

    return $model;

  }

}
