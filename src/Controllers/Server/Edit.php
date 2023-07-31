<?php

namespace BNETDocs\Controllers\Server;

use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Server\Form as FormModel;
use \OutOfBoundsException;

class Edit extends \BNETDocs\Controllers\Base
{
  public const S_DISABLED = ':no_entry: Disabled';
  public const S_ONLINE   = ':white_check_mark: Online';
  public const S_OFFLINE  = ':x: Offline';

  public function __construct()
  {
    $this->model = new FormModel();
  }

  public function invoke(?array $args): bool
  {
    $this->model->server_edit = true;

    if (!($this->model->active_user && $this->model->active_user->getOption(\BNETDocs\Libraries\User::OPTION_ACL_SERVER_MODIFY)))
    {
      $this->model->_responseCode = 403;
      $this->model->error = FormModel::ERROR_ACCESS_DENIED;
      return true;
    }

    $this->model->form = Router::query();
    $id = $this->model->form['id'] ?? null;
    if (!is_numeric($id))
    {
      $this->model->_responseCode = 400;
      $this->model->error = FormModel::ERROR_INVALID_ID;
      return true;
    }
    $id = (int) $id;

    try { $this->model->server = new \BNETDocs\Libraries\Server($id); }
    catch (\UnexpectedValueException) { $this->model->server = null; }

    if (!$this->model->server)
    {
      $this->model->_responseCode = 404;
      return true;
    }

    $this->model->_responseCode = 200;
    $this->model->server_types = \BNETDocs\Libraries\ServerType::getAllServerTypes();
    if (Router::requestMethod() == Router::METHOD_POST) $this->handlePost();
    return true;
  }

  protected function handlePost(): void
  {
    $q = &$this->model->form;

    try { $this->model->server->setAddress($q['address'] ?? ''); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_INVALID_ADDRESS; return; }

    try { $this->model->server->setLabel(isset($q['label']) && !empty($q['label']) ? $q['label'] : null); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_INVALID_LABEL; return; }

    try { $this->model->server->setPort(isset($q['port']) ? (int) $q['port'] : 0); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_INVALID_PORT; return; }

    try { $this->model->server->setTypeId(isset($q['type']) ? (int) $q['type'] : 0); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_INVALID_TYPE; return; }

    $this->model->server->setDisabled((bool) ($this->model->form['disabled'] ?? null));
    $this->model->server->setOnline((bool) ($this->model->form['online'] ?? null));

    $this->model->error = $this->model->server->commit() ? FormModel::ERROR_SUCCESS : FormModel::ERROR_INTERNAL;

    if ($this->model->error === FormModel::ERROR_SUCCESS)
    {
      $event = Logger::initEvent(
        \BNETDocs\Libraries\EventLog\EventTypes::SERVER_EDITED,
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
