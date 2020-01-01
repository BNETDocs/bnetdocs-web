<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\CreatePassword as UserCreatePasswordModel;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class CreatePassword extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UserCreatePasswordModel();

    $data = $router->getRequestBodyArray();

    $model->input = (
      isset($data['input']) ? $data['input'] : null
    );
    $model->output = (
      !is_null($model->input) ? self::transform($model->input) : null
    );

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();

    return $model;
  }

  private static function transform($input) {
    $hash = null;
    $salt = null;

    User::createPassword( $input, $hash, $salt );

    return [ $hash, $salt ];
  }

}
