<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Authentication;
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

    if ($router->getRequestMethod() == 'POST') {
      $this->tryChangePassword($router, $model);
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }

  protected function tryChangePassword(
    Router &$router, UserChangePasswordModel &$model
  ) {
    if ( !isset( Authentication::$user )) {
      $model->error = 'NOT_LOGGED_IN';
      return;
    }
    $data       = $router->getRequestBodyArray();
    $csrf_id    = (isset($data['csrf_id'   ]) ? $data['csrf_id'   ] : null);
    $csrf_token = (isset($data['csrf_token']) ? $data['csrf_token'] : null);
    $csrf_valid = CSRF::validate($csrf_id, $csrf_token);
    if (!$csrf_valid) {
      $model->error = 'INVALID_CSRF';
      return;
    }
    CSRF::invalidate($csrf_id);
    $pw1 = (isset($data['pw1']) ? $data['pw1'] : null);
    $pw2 = (isset($data['pw2']) ? $data['pw2'] : null);
    $pw3 = (isset($data['pw3']) ? $data['pw3'] : null);
    if ($pw2 !== $pw3) {
      $model->error = 'NONMATCHING_PASSWORD';
      return;
    }
    if ( !Authentication::$user->checkPassword( $pw1 )) {
      $model->error = 'PASSWORD_INCORRECT';
      return;
    }
    $pwlen = strlen($pw2);
    $req = &Common::$config->bnetdocs->user_register_requirements;
    $email = Authentication::$user->getEmail();
    $username = Authentication::$user->getUsername();
    if (!$req->password_allow_email && stripos($pw2, $email)) {
      $model->error = 'PASSWORD_CONTAINS_EMAIL';
      return;
    }
    if (!$req->password_allow_username && stripos($pw2, $username)) {
      $model->error = 'PASSWORD_CONTAINS_USERNAME';
      return;
    }
    if (is_numeric($req->password_length_max)
      && $pwlen > $req->password_length_max) {
      $model->error = 'PASSWORD_TOO_LONG';
      return;
    }
    if (is_numeric($req->password_length_min)
      && $pwlen < $req->password_length_min) {
      $model->error = 'PASSWORD_TOO_SHORT';
      return;
    }
    $blacklist = Common::$config->bnetdocs->user_password_blacklist;
    foreach ($blacklist as $blacklist_pw) {
      if (strtolower($blacklist_pw->password) == strtolower($pw2)) {
        $model->error = 'PASSWORD_BLACKLIST';
        $model->error_extra = $blacklist_pw->reason;
        return;
      }
    }
    $old_password_hash = Authentication::$user->getPasswordHash();
    $old_password_salt = Authentication::$user->getPasswordSalt();
    try {
      $success = Authentication::$user->changePassword($pw2);
    } catch (QueryException $e) {
      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);
    }
    $new_password_hash = Authentication::$user->getPasswordHash();
    $new_password_salt = Authentication::$user->getPasswordSalt();
    if (!$success) {
      $model->error = 'INTERNAL_ERROR';
    } else {
      $model->error = false;
    }
    Logger::logEvent(
      EventTypes::USER_PASSWORD_CHANGE,
      Authentication::$user->getId(),
      getenv('REMOTE_ADDR'),
      json_encode([
        'error'             => $model->error,
        'old_password_hash' => $old_password_hash,
        'old_password_salt' => $old_password_salt,
        'new_password_hash' => $new_password_hash,
        'new_password_salt' => $new_password_salt
      ])
    );
  }
}
