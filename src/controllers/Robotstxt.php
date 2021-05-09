<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Models\Robotstxt as RobotstxtModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Robotstxt extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new RobotstxtModel();
    $model->rules = Common::$config->bnetdocs->robotstxt;
    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
