<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\CSRF;
use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\RecaptchaException;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Logger;
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
    $model->captcha_key  = Common::$config->recaptcha->sitekey;
    $model->error        = null;
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
    if (!self::verifyCaptcha($captcha)) {
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
      $success = User::create($email, $username, null, $pw1, 0);
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

  protected static function verifyCaptcha($g_captcha_response) {
    $data = [
      "secret"   => Common::$config->recaptcha->secret,
      "response" => $g_captcha_response,
      "remoteip" => getenv("REMOTE_ADDR"),
    ];
    $r = Common::curlRequest(Common::$config->recaptcha->url, $data);
    Logger::logMetric("response_code", $r->code);
    Logger::logMetric("response_type", $r->type);
    Logger::logMetric("response_data", $r->data);
    if ($r->code != 200)
      throw new RecaptchaException("Received bad HTTP status");
    if (stripos($r->type, "json") === false)
      throw new RecaptchaException("Received unknown content type");
    if (empty($data))
      throw new RecaptchaException("Received empty response");
    $j = json_decode($r->data);
    $e = json_last_error();
    Logger::logMetric("json_last_error", $e);
    if (!$j || $e !== JSON_ERROR_NONE || !property_exists($j, "success"))
      throw new RecaptchaException("Received invalid response");
    return ($j->success);
  }

}
