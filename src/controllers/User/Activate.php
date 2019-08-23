<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Models\User\Activate as UserActivateModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Activate extends Controller {

  public function &run( Router &$router, View &$view, array &$args ) {

    $model = new UserActivateModel();

    $data = $router->getRequestQueryArray();

    $model->token = isset( $data[ 't' ] ) ? $data[ 't' ] : null;
    $model->error = 'INVALID_TOKEN';

    $view->render( $model );

    $model->_responseCode = 200;
    $model->_responseHeaders[ 'Content-Type' ] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
