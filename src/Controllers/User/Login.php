<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\Login as UserLoginModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Login extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UserLoginModel();
    $model->error = null;

    if ($router->getRequestMethod() == 'POST') {
      $this->tryLogin($router, $model);
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }

  protected function tryLogin(Router &$router, UserLoginModel &$model) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $data = $router->getRequestBodyArray();
    $email = (isset($data['email'     ]) ? $data['email'     ] : null);
    $password = (isset($data['password'  ]) ? $data['password'  ] : null);

    $model->email = $email;

    if ( isset( Authentication::$user )) {
      $model->error = 'ALREADY_LOGGED_IN';
    } else if (empty($email)) {
      $model->error = 'EMPTY_EMAIL';
    } else if (Common::$config->bnetdocs->user_login_disabled) {
      $model->error = 'LOGIN_DISABLED';
    }

    if ($model->error) return;

    try {
      $user = new User(User::findIdByEmail($email));
    } catch (UserNotFoundException $e) {
      $user = null;
    }

    if (!$user) {
      $model->error = 'USER_NOT_FOUND';
    } else if ($user->isDisabled()) {
      $model->error = 'USER_DISABLED';
    } else if (!$user->checkPassword($password)) {
      $model->error = 'PASSWORD_INCORRECT';
    } else if (!$user->isVerified()) {
      $model->error = 'USER_NOT_VERIFIED';
    }

    if ($model->error) return;
    $model->error = false;

    // Upgrade old password (we checked it matches earlier above)
    if (substr($user->getPasswordHash(), 0, 1) !== '$') {
      $user->setPassword($password);
      $user->commit();
    }

    Authentication::login($user);

    Logger::logEvent(
      EventTypes::USER_LOGIN,
      ($user ? $user->getId() : null),
      getenv('REMOTE_ADDR'),
      json_encode([
        'error' => $model->error,
        'email' => $model->email,
      ])
    );
  }
}
