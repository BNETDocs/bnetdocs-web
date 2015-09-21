<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\DatabaseDriver;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\Login as UserLoginModel;
use \BNETDocs\Views\User\LoginHtml as UserLoginHtmlView;

class Login extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new UserLoginHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new UserLoginModel();
    $ttl   = 300;
    if ($router->getRequestMethod() == "POST") {
      $ttl = 0;
      $this->tryLogin($router, $model);
    }
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL($ttl);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function tryLogin(Router &$router, UserLoginModel &$model) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }
    $data            = $router->getRequestBodyArray();
    $model->email    = (isset($data["email"   ]) ? $data["email"   ] : null);
    $model->password = (isset($data["password"]) ? $data["password"] : null);
    if (empty($model->email)) {
      $model->bad_email = "Email address was left blank.";
    } else {
      $user = null;
      try {
        $user = new User(User::findIdByEmail($model->email));
      } catch (UserNotFoundException $e) {
        $user = null;
      }
      if (!$user) {
        $success = $this->loginErrorNotFound($model);
      } else if ($user->getOptionsBitmask() & User::OPTION_DISABLED) {
        $success = $this->loginErrorDisabled($model);
      } else if (!$user->getOptionsBitmask() & User::OPTION_VERIFIED) {
        $success = $this->loginErrorNotVerified($model);
      } else if (!$user->checkPassword($model->password)) {
        $success = $this->loginErrorWrongPassword($model);
      } else if (Common::$config->bnetdocs->user_login_disabled) {
        $success = $this->loginErrorSiteDisabled($model);
      } else {
        $success = $this->loginErrorNYI($model);
      }
      Logger::logEvent(
        "user_login",
        ($user ? $user->getId() : null),
        getenv("REMOTE_ADDR"),
        json_encode([
          "success"      => $success,
          "email"        => $model->email,
          "bad_email"    => $model->bad_email,
          "bad_password" => $model->bad_password
        ])
      );
    }
  }

  private function loginErrorNotFound(UserLoginModel &$model) {
    $model->bad_email = "There is no account by that email address.";
    return false;
  }

  private function loginErrorDisabled(UserLoginModel &$model) {
    $model->bad_email = "Your account has been disabled administratively.";
    $model->bad_password = true;
    return false;
  }

  private function loginErrorNotVerified(UserLoginModel &$model) {
    $model->bad_email = "Your account has not been verified yet.";
    $model->bad_password = true;
    return false;
  }

  private function loginErrorWrongPassword(UserLoginModel &$model) {
    $model->bad_email = "Incorrect email address or password.";
    $model->bad_password = true;
    return false;
  }

  private function loginErrorSiteDisabled(UserLoginModel &$model) {
    $model->bad_email = "Log in has been disabled administratively for everyone.";
    $model->bad_password = true;
    return false;
  }

  private function loginErrorNYI(UserLoginModel &$model) {
    $model->bad_email = "Log in has not yet been implemented.";
    $model->bad_password = true;
    return false;
  }

}
