<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Models\Document\Search as DocumentSearchModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Search extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new DocumentSearchModel();
    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
