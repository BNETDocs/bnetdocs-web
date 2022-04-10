<?php
namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\CreatePassword as UserCreatePasswordModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class CreatePassword extends Controller
{
  public function &run(Router &$router, View &$view, array &$args)
  {
    $model = new UserCreatePasswordModel();
    $data = $router->getRequestBodyArray();

    $model->input = (
      isset($data['input']) ? $data['input'] : null
    );

    $model->output = (
      !is_null($model->input) ? User::createPassword($model->input) : null
    );

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }
}
