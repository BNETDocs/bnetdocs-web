<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\CSRF;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\User\ChangePassword as UserChangePasswordModel;
use \BNETDocs\Views\User\ChangePasswordHtml as UserChangePasswordHtmlView;

class ChangePassword extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new UserChangePasswordHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new UserChangePasswordModel();
    $model->csrf_id      = mt_rand();
    $model->csrf_token   = CSRF::generate($model->csrf_id);
    $model->error        = null;
    $model->user_session = UserSession::load($router);
    if ($router->getRequestMethod() == "POST") {
      $this->tryChangePassword($router, $model);
    }
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function tryChangePassword(
    Router &$router, UserChangePasswordModel &$model
  ) {
    if (!isset($model->user_session)) {
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
    $user = new User($model->user_session->user_id);
    if (!$user->checkPassword($pw1)) {
      $model->error = "PASSWORD_INCORRECT";
      return;
    }
    $old_password_hash = $user->getPasswordHash();
    $old_password_salt = $user->getPasswordSalt();
    $success           = $user->changePassword($pw2);
    $new_password_hash = $user->getPasswordHash();
    $new_password_salt = $user->getPasswordSalt();
    if (!$success) {
      $model->error = "INTERNAL_ERROR";
    } else {
      $model->error = false;
    }
    Logger::logEvent(
      "user_pw_change",
      $model->user_session->user_id,
      getenv("REMOTE_ADDR"),
      json_encode([
        "error" => $model->error,
        "old_password_hash" => $old_password_hash,
        "old_password_salt" => $old_password_salt,
        "new_password_hash" => $new_password_hash,
        "new_password_salt" => $new_password_salt
      ])
    );
  }

}
