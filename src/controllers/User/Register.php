<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\CSRF;
use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\RecaptchaException;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Recaptcha;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\User\Register as UserRegisterModel;
use \BNETDocs\Views\User\RegisterHtml as UserRegisterHtmlView;

class Register extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new UserRegisterHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new UserRegisterModel();
    $model->csrf_id      = mt_rand();
    $model->csrf_token   = CSRF::generate($model->csrf_id);
    $model->error        = null;
    $model->recaptcha    = new Recaptcha(
      Common::$config->recaptcha->secret,
      Common::$config->recaptcha->sitekey,
      Common::$config->recaptcha->url
    );
    $model->user_session = UserSession::load($router);
    if ($router->getRequestMethod() == "POST") {
      $this->tryRegister($router, $model);
    }
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function tryRegister(Router &$router, UserRegisterModel &$model) {
    $data = $router->getRequestBodyArray();
    $model->email    = (isset($data["email"   ]) ? $data["email"   ] : null);
    $model->username = (isset($data["username"]) ? $data["username"] : null);
    if (isset($model->user_session)) {
      $model->error = "ALREADY_LOGGED_IN";
      return;
    }
    $csrf_id    = (isset($data["csrf_id"   ]) ? $data["csrf_id"   ] : null);
    $csrf_token = (isset($data["csrf_token"]) ? $data["csrf_token"] : null);
    $csrf_valid = CSRF::validate($csrf_id, $csrf_token);
    if (!$csrf_valid) {
      $model->error = "INVALID_CSRF";
      return;
    }
    CSRF::invalidate($csrf_id);
    $email    = $model->email;
    $username = $model->username;
    $pw1      = (isset($data["pw1"]) ? $data["pw1"] : null);
    $pw2      = (isset($data["pw2"]) ? $data["pw2"] : null);
    $captcha  = (
      isset($data["g-recaptcha-response"]) ?
      $data["g-recaptcha-response"]        :
      null
    );
    try {
      if (!$model->recaptcha->verify($captcha, getenv("REMOTE_ADDR"))) {
        $model->error = "INVALID_CAPTCHA";
        return;
      }
    } catch (RecaptchaException $e) {
      $model->error = "INVALID_CAPTCHA";
      return;
    }
    if ($pw1 !== $pw2) {
      $model->error = "NONMATCHING_PASSWORD";
      return;
    }
    $pwlen       = strlen($pw1);
    $usernamelen = strlen($username);
    $req = Common::$config->bnetdocs->user_register_requirements;
    if ($req->email_validate_quick
      && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $model->error = "INVALID_EMAIL";
      return;
    }
    if (!$req->password_allow_email && stripos($pw1, $email)) {
      $model->error = "PASSWORD_CONTAINS_EMAIL";
      return;
    }
    if (!$req->password_allow_username && stripos($pw1, $username)) {
      $model->error = "PASSWORD_CONTAINS_USERNAME";
      return;
    }
    if (is_numeric($req->username_length_max)
      && $usernamelen > $req->username_length_max) {
      $model->error = "USERNAME_TOO_LONG";
      return;
    }
    if (is_numeric($req->username_length_min)
      && $usernamelen < $req->username_length_min) {
      $model->error = "USERNAME_TOO_SHORT";
      return;
    }
    if (is_numeric($req->password_length_max)
      && $pwlen > $req->password_length_max) {
      $model->error = "PASSWORD_TOO_LONG";
      return;
    }
    if (is_numeric($req->password_length_min)
      && $pwlen < $req->password_length_min) {
      $model->error = "PASSWORD_TOO_SHORT";
      return;
    }
    if (Common::$config->bnetdocs->user_register_disabled) {
      $model->error = "REGISTER_DISABLED";
      return;
    }

    try {
      if (!$req->email_duplicate_allowed && User::findIdByEmail($email)) {
        $model->error = "EMAIL_ALREADY_USED";
        return;
      }
    } catch (UserNotFoundException $e) {}

    try {

      $success = User::create(
        $email, $username, null, $pw1, User::DEFAULT_OPTION
      );

    } catch (QueryException $e) {

      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);

    }

    if (!$success) {
      $model->error = "INTERNAL_ERROR";
    } else {
      $model->error = false;
    }
    Logger::logEvent(
      "user_created",
      null,
      getenv("REMOTE_ADDR"),
      json_encode([
        "error"           => $model->error,
        "requirements"    => $req,
        "email"           => $email,
        "username"        => $username,
        "display_name"    => null,
        "options_bitmask" => 0,
      ])
    );
  }

}
