<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\User\User;
use \BNETDocs\Models\User\Login as LoginModel;

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
      $this->model->_responseCode = HttpCode::HTTP_BAD_REQUEST;
      $this->model->error = LoginModel::ERROR_ALREADY_LOGGED_IN;
      return true;
    }

    $this->model->_responseCode = HttpCode::HTTP_OK;
    $this->model->error = null;

    $q = Router::query();
    $this->model->email = $q['email'] ?? null;
    $this->model->password = $q['password'] ?? null;

    if (Router::requestMethod() != Router::METHOD_POST) return true;

    if (empty($this->model->email))
      $this->model->error = LoginModel::ERROR_EMPTY_EMAIL;
    else if (empty($this->model->password))
      $this->model->error = LoginModel::ERROR_EMPTY_PASSWORD;
    else if (\BNETDocs\Libraries\Core\Config::get('bnetdocs.user_login_disabled'))
      $this->model->error = LoginModel::ERROR_SYSTEM_DISABLED;

    if ($this->model->error) return true;

    try { $this->model->user = new User(User::findIdByEmail($this->model->email)); }
    catch (\BNETDocs\Exceptions\UserNotFoundException) { $this->model->user = null; }

    if (!$this->model->user)
      $this->model->error = LoginModel::ERROR_USER_NOT_FOUND;
    else if ($this->model->user->isDisabled())
      $this->model->error = LoginModel::ERROR_USER_DISABLED;
    else if (\is_null($this->model->user->getPasswordHash()))
      $this->model->error = LoginModel::ERROR_PASSWORD_RESET_REQUIRED;
    else if (!$this->model->user->checkPassword($this->model->password))
      $this->model->error = LoginModel::ERROR_INCORRECT_PASSWORD;
    else if (!$this->model->user->isVerified())
      $this->model->error = LoginModel::ERROR_USER_NOT_VERIFIED;

    if ($this->model->error) return true;

    // Upgrade a legacy hash or an outdated bcrypt cost (we checked it matches earlier above)
    if ($this->model->user->needsPasswordRehash())
    {
      $this->model->user->setPassword($this->model->password);
      if (!$this->model->user->commit())
      {
        $this->model->_responseCode = HttpCode::HTTP_INTERNAL_SERVER_ERROR;
        $this->model->error = LoginModel::ERROR_INTERNAL;
        return true;
      }
    }

    \BNETDocs\Libraries\User\Authentication::login($this->model->user);
    $this->model->error = false;

    $event = Logger::initEvent(
      \BNETDocs\Libraries\EventLog\EventTypes::USER_LOGIN,
      $this->model->user,
      getenv('REMOTE_ADDR'),
      [
        'error' => $this->model->error,
        'email' => $this->model->email,
      ]
    );

    if ($event->commit())
    {
      $embed = Logger::initDiscordEmbed($event, $this->model->user->getURI());
      Logger::logToDiscord($event, $embed);
    }

    return true;
  }
}
