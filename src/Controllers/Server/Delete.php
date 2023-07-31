<?php

namespace BNETDocs\Controllers\Server;

use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Server\Delete as DeleteModel;

class Delete extends \BNETDocs\Controllers\Base
{
  public const S_DISABLED = ':no_entry: Disabled';
  public const S_ONLINE   = ':white_check_mark: Online';
  public const S_OFFLINE  = ':x: Offline';

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
      $event = Logger::initEvent(
        \BNETDocs\Libraries\EventLog\EventTypes::SERVER_DELETED,
        $this->model->active_user,
        getenv('REMOTE_ADDR'),
        $this->model->server
      );

      if ($event->commit())
      {
        $embed = Logger::initDiscordEmbed($event, $this->model->server->getURI(), [
          'Type' => $this->model->server->getType()->getLabel(),
          'Label' => $this->model->server->getLabel(),
          'Server' => $this->model->server->getAddress() . ':' . $this->model->server->getPort(),
          'Status' => $this->model->server->isDisabled() ? self::S_DISABLED : (
            $this->model->server->isOnline() ? self::S_ONLINE : self::S_OFFLINE
          ),
        ]);
        Logger::logToDiscord($event, $embed);
      }
    }
  }
}
