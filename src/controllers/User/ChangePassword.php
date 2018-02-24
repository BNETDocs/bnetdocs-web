<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\CSRF;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\ChangePassword as UserChangePasswordModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class ChangePassword extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model               = new UserChangePasswordModel();
    $model->csrf_id      = mt_rand();
    $model->csrf_token   = CSRF::generate($model->csrf_id);
    $model->error        = null;

    if ($router->getRequestMethod() == "POST") {
      $this->tryChangePassword($router, $model);
    }

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

  protected function tryChangePassword(
    Router &$router, UserChangePasswordModel &$model
  ) {
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
    $pw1 = (isset($data["pw1"]) ? $data["pw1"] : null);
    $pw2 = (isset($data["pw2"]) ? $data["pw2"] : null);
    $pw3 = (isset($data["pw3"]) ? $data["pw3"] : null);
    if ($pw2 !== $pw3) {
      $model->error = "NONMATCHING_PASSWORD";
      return;
    }
    $user = new User($_SESSION['user_id']);
    if (!$user->checkPassword($pw1)) {
      $model->error = "PASSWORD_INCORRECT";
      return;
    }
    $old_password_hash = $user->getPasswordHash();
    $old_password_salt = $user->getPasswordSalt();
    try {
      $success = $user->changePassword($pw2);
    } catch (QueryException $e) {
      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);
    }
    $new_password_hash = $user->getPasswordHash();
    $new_password_salt = $user->getPasswordSalt();
    if (!$success) {
      $model->error = "INTERNAL_ERROR";
    } else {
      $model->error = false;
    }
    Logger::logEvent(
      EventTypes::USER_PASSWORD_CHANGE,
      $_SESSION['user_id'],
      getenv("REMOTE_ADDR"),
      json_encode([
        "error"             => $model->error,
        "old_password_hash" => $old_password_hash,
        "old_password_salt" => $old_password_salt,
        "new_password_hash" => $new_password_hash,
        "new_password_salt" => $new_password_salt
      ])
    );
  }

}
