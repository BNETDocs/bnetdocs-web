<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\Router;

class Logout extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\User\Logout();
  }

  public function invoke(?array $args): bool
  {
    if (!$this->model->active_user)
    {
      $this->_responseCode = 400;
      return true;
    }

    $this->model->_responseCode = 200;
    $this->model->error = false;
    if (Router::requestMethod() == Router::METHOD_POST) $this->tryLogout();
    return true;
  }

  protected function tryLogout() : void
  {
    $user_id = $this->model->active_user->getId();
    if (Authentication::logout()) $this->model->active_user = &Authentication::$user;
    \BNETDocs\Libraries\Logger::logEvent(
      \BNETDocs\Libraries\EventTypes::USER_LOGOUT,
      $user_id,
      getenv('REMOTE_ADDR'),
      json_encode(['error' => $this->model->error])
    );
  }
}
