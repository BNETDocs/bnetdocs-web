<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Models\User\Logout as UserLogoutModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Logout extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UserLogoutModel();
    $model->error = null;

    if ($router->getRequestMethod() == 'POST') {
      $this->tryLogout($router, $model);
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }

  protected function tryLogout(Router &$router, UserLogoutModel &$model) {
    if ( !isset( Authentication::$user )) {
      $model->error = 'NOT_LOGGED_IN';
      return;
    }
    $data = $router->getRequestBodyArray();
    $model->error = false;
    $user_id = Authentication::$user->getId();
    Authentication::logout();
    Logger::logEvent(
      EventTypes::USER_LOGOUT,
      $user_id,
      getenv('REMOTE_ADDR'),
      json_encode(['error' => $model->error])
    );
  }
}
