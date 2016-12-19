<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\CSRF;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Models\User\Logout as UserLogoutModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Logout extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model               = new UserLogoutModel();
    $model->csrf_id      = mt_rand();
    $model->csrf_token   = CSRF::generate($model->csrf_id);
    $model->error        = null;

    if ($router->getRequestMethod() == "POST") {
      $this->tryLogout($router, $model);
    }

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

  protected function tryLogout(Router &$router, UserLogoutModel &$model) {
    if (!isset($_SESSION['user_id'])) {
      $model->error = "NOT_LOGGED_IN";
      return;
    }
    $data       = $router->getRequestBodyArray();
    $csrf_id    = (isset($data["csrf_id"   ]) ? $data["csrf_id"   ] : null);
    $csrf_token = (isset($data["csrf_token"]) ? $data["csrf_token"] : null);
    $csrf_valid = CSRF::validate($csrf_id, $csrf_token);
    if (!$csrf_valid) {
      $model->error = "INVALID_CSRF";
      return;
    }
    CSRF::invalidate($csrf_id);
    $model->error = false;
    $user_id      = $_SESSION['user_id'];
    unset($_SESSION['user_id']);
    Logger::logEvent(
      "user_logout",
      $user_id,
      getenv("REMOTE_ADDR"),
      json_encode(["error" => $model->error])
    );
  }

}
