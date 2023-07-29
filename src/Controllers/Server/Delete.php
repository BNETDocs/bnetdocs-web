<?php

namespace BNETDocs\Controllers\Server;

use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Server\Delete as DeleteModel;

class Delete extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new DeleteModel();
  }

  public function invoke(?array $args): bool
  {
    if (!($this->model->active_user && $this->model->active_user->getOption(\BNETDocs\Libraries\User::OPTION_ACL_SERVER_DELETE)))
    {
      $this->model->_responseCode = 403;
      $this->model->error = DeleteModel::ERROR_ACCESS_DENIED;
      return true;
    }

    $id = Router::query()['id'] ?? null;
    if (!is_numeric($id))
    {
      $this->model->_responseCode = 400;
      $this->model->error = DeleteModel::ERROR_INVALID_ID;
      return true;
    }
    $id = (int) $id;

    try { $this->model->server = new \BNETDocs\Libraries\Server($id); }
    catch (\UnexpectedValueException) { $this->model->server = null; }

    if (!$this->model->server)
    {
      $this->model->_responseCode = 404;
      $this->model->error = DeleteModel::ERROR_INVALID_ID;
      return true;
    }

    $this->model->_responseCode = 200;
    if (Router::requestMethod() == Router::METHOD_POST) $this->handlePost();
    return true;
  }

  protected function handlePost(): void
  {
    $this->model->error = $this->model->server->deallocate() ? DeleteModel::ERROR_SUCCESS : DeleteModel::ERROR_INTERNAL;
    if ($this->model->error === DeleteModel::ERROR_SUCCESS)
    {
      \BNETDocs\Libraries\EventLog\Event::log(
        \BNETDocs\Libraries\EventLog\EventTypes::SERVER_DELETED,
        $this->model->active_user,
        getenv('REMOTE_ADDR'),
        $this->model->server
      );
    }
  }
}
