<?php

namespace BNETDocs\Controllers\EventLog;

class View extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\EventLog\View();
  }

  public function invoke(?array $args): bool
  {
    $this->model->acl_allowed = $this->model->active_user
    && $this->model->active_user->getOption(\BNETDocs\Libraries\User::OPTION_ACL_EVENT_LOG_VIEW);

    if (!$this->model->acl_allowed)
    {
      $this->model->_responseCode = 403;
      return true;
    }

    $q = \BNETDocs\Libraries\Router::query();
    $this->model->id = isset($q['id']) ? (int) $q['id'] : null;

    try { if (!is_null($this->model->id)) $this->model->event = new \BNETDocs\Libraries\EventLog\Event($this->model->id); }
    catch (\UnexpectedValueException) { $this->model->event = null; }

    $this->model->_responseCode = $this->model->event ? 200 : 404;
    return true;
  }
}
