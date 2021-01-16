<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\CreatePassword as UserCreatePasswordModel;

use \CarlBennett\MVC\Libraries\Common;
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
    return $model;
  }

  private static function transform($input) {
    $hash = null;
    $salt = null;

    $pepper = Common::$config->bnetdocs->user_password_pepper;

    $gmp  = gmp_init(time());
    $gmp  = gmp_mul($gmp, mt_rand());
    $gmp  = gmp_mul($gmp, gmp_random_bits(64));
    $salt = strtoupper(gmp_strval($gmp, 36));

    $hash = strtoupper(hash('sha256', $input.$salt.$pepper));

    return [ $hash, $salt ];
  }
}
