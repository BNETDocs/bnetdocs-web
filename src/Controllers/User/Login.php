<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\Login as LoginModel;
use \CarlBennett\MVC\Libraries\Common;

class Login extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new LoginModel();
  }

  public function invoke(?array $args): bool
  {
    if ($this->model->active_user)
    {
      $this->model->_responseCode = 400;
      $this->model->error = LoginModel::ERROR_ALREADY_LOGGED_IN;
      return true;
    }

    $this->model->_responseCode = 200;
    $this->model->error = LoginModel::ERROR_NONE;

    $q = Router::query();
    $this->model->email = $q['email'] ?? null;
    $this->model->password = $q['password'] ?? null;

    if (Router::requestMethod() != Router::METHOD_POST) return true;

    if (empty($this->model->email))
      $this->model->error = LoginModel::ERROR_EMPTY_EMAIL;
    else if (empty($this->model->password))
      $this->model->error = LoginModel::ERROR_EMPTY_PASSWORD;
    else if (Common::$config->bnetdocs->user_login_disabled)
      $this->model->error = LoginModel::ERROR_SYSTEM_DISABLED;

    if ($this->model->error !== LoginModel::ERROR_NONE) return true;

    try { $this->model->user = new User(User::findIdByEmail($this->model->email)); }
    catch (\UnexpectedValueException) { $this->model->user = null; }

    if (!$this->model->user)
      $this->model->error = LoginModel::ERROR_USER_NOT_FOUND;
    else if ($this->model->user->isDisabled())
      $this->model->error = LoginModel::ERROR_USER_DISABLED;
    else if (!$this->model->user->checkPassword($this->model->password))
      $this->model->error = LoginModel::ERROR_INCORRECT_PASSWORD;
    else if (!$this->model->user->isVerified())
      $this->model->error = LoginModel::ERROR_USER_NOT_VERIFIED;

    if ($this->model->error !== LoginModel::ERROR_NONE) return true;

    // Upgrade old password (we checked it matches earlier above)
    if (substr($this->model->user->getPasswordHash(), 0, 1) !== '$')
    {
      $this->model->user->setPassword($this->model->password);
      if (!$this->model->user->commit())
      {
        $this->model->_responseCode = 500;
        $this->model->error = LoginModel::ERROR_INTERNAL;
        return true;
      }
    }

    \BNETDocs\Libraries\Authentication::login($this->model->user);
    $this->model->error = LoginModel::ERROR_SUCCESS;

    \BNETDocs\Libraries\Event::log(
      \BNETDocs\Libraries\EventTypes::USER_LOGIN,
      $this->model->user,
      getenv('REMOTE_ADDR'),
      [
        'error' => $this->model->error,
        'email' => $this->model->email,
      ]
    );

    return true;
  }
}
