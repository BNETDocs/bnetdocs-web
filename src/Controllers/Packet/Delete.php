<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Router;

class Delete extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Packet\Delete();
  }

  public function invoke(?array $args): bool
  {
    $this->model->acl_allowed = $this->model->active_user
      && $this->model->active_user->getOption(\BNETDocs\Libraries\User::OPTION_ACL_PACKET_DELETE);

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = 401;
      $this->model->error = 'ACL_NOT_SET';
      return true;
    }

    $q = Router::query();
    $this->model->id = $q['id'] ?? null;

    try { if (!is_null($this->model->id)) $this->model->packet = new \BNETDocs\Libraries\Packet($this->model->id); }
    catch (\UnexpectedValueException) { $this->model->packet = null; }

    if (!$this->model->packet)
    {
      $this->model->_responseCode = 404;
      $this->model->error = 'NOT_FOUND';
      return true;
    }

    $this->model->title = $this->model->packet->getLabel();

    if (Router::requestMethod() == Router::METHOD_POST) $this->tryDelete();
    $this->model->_responseCode = $this->model->error ? 500 : 200;
    return true;
  }

  protected function tryDelete(): void
  {
    $this->model->error = $this->model->packet->deallocate() ? false : 'INTERNAL_ERROR';

    \BNETDocs\Libraries\EventLog\Event::log(
      \BNETDocs\Libraries\EventLog\EventTypes::PACKET_DELETED,
      $this->model->active_user,
      getenv('REMOTE_ADDR'),
      [
        'error' => $this->model->error,
        'packet_id' => $this->model->id,
        'packet' => $this->model->packet,
      ]
    );
  }
}
