<?php

namespace BNETDocs\Controllers\Server;

use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Models\Server\Form as FormModel;
use \OutOfBoundsException;

class Create extends \BNETDocs\Controllers\Base
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
    if (!($this->model->active_user && $this->model->active_user->getOption(\BNETDocs\Libraries\User\User::OPTION_ACL_SERVER_CREATE)))
    {
      $this->model->_responseCode = HttpCode::HTTP_FORBIDDEN;
      $this->model->error = $this->model->active_user ? FormModel::ERROR_ACL_NOT_SET : FormModel::ERROR_NOT_LOGGED_IN;
      return true;
    }

    $this->model->_responseCode = HttpCode::HTTP_OK;
    $this->model->form_fields = Router::query();
    $this->model->server = new \BNETDocs\Libraries\Server\Server(null);
    $this->model->server_types = \BNETDocs\Libraries\Server\Type::getAllServerTypes();
    if (Router::requestMethod() == Router::METHOD_POST) $this->handlePost();
    return true;
  }

  protected function handlePost(): void
  {
    $q = &$this->model->form_fields;
    $this->model->server->setUser($this->model->active_user);

    try { $this->model->server->setAddress($q['address'] ?? ''); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_INVALID_ADDRESS; return; }

    try { $this->model->server->setLabel(isset($q['label']) && !empty($q['label']) ? $q['label'] : null); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_INVALID_LABEL; return; }

    try { $this->model->server->setPort(isset($q['port']) ? (int) $q['port'] : 0); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_INVALID_PORT; return; }

    try { $this->model->server->setTypeId(isset($q['type']) ? (int) $q['type'] : 0); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_INVALID_TYPE; return; }

    $this->model->server->setDisabled((bool) ($this->model->form_fields['disabled'] ?? null));
    $this->model->server->setOnline((bool) ($this->model->form_fields['online'] ?? null));

    $this->model->error = $this->model->server->commit() ? false : FormModel::ERROR_INTERNAL;

    if ($this->model->error === false)
    {
      $event = Logger::initEvent(
        \BNETDocs\Libraries\EventLog\EventTypes::SERVER_CREATED,
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
