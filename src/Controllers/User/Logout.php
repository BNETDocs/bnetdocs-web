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
      $this->model->_responseCode = 400;
      return true;
    }

    $this->model->_responseCode = 200;
    $this->model->error = false;
    if (Router::requestMethod() == Router::METHOD_POST) $this->tryLogout();
    return true;
  }

  protected function tryLogout(): void
  {
    $user = $this->model->active_user;
    if (Authentication::logout()) $this->model->active_user = &Authentication::$user;
    \BNETDocs\Libraries\Event::log(
      \BNETDocs\Libraries\EventTypes::USER_LOGOUT, $user, getenv('REMOTE_ADDR'), ['error' => $this->model->error]
    );
  }
}
