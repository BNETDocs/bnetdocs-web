<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Models\FrontPage as FrontPageModel;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class FrontPage extends Controller {

  public function &run( Router &$router, View &$view, array &$args ) {

    $model = new FrontPageModel();

    $view->render( $model );

    $model->_responseCode = 200;
    $model->_responseHeaders['Content-Type'] = $view->getMimeType();
    $model->_responseTTL = 300;

    return $model;

  }

}
