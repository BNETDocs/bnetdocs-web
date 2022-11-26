<?php

namespace BNETDocs\Controllers\EventLog;

use \BNETDocs\Libraries\Event;

class Index extends \BNETDocs\Controllers\Base
{
  const PAGINATION_LIMIT_DEF = 15;  // The default amount of items per page.
  const PAGINATION_LIMIT_MIN = 5;   // The least amount of items per page.
  const PAGINATION_LIMIT_MAX = 250; // The most amount of items per page.

  public function __construct()
  {
    $this->model = new \BNETDocs\Models\EventLog\Index();
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
    $this->model->order = $q['order'] ?? 'datetime-desc';

    switch ($this->model->order)
    {
      case 'id-asc': $order = ['id', 'ASC']; break;
      case 'id-desc': $order = ['id', 'DESC']; break;
      case 'datetime-asc': $order = ['event_datetime', 'ASC']; break;
      case 'datetime-desc': $order = ['event_datetime', 'DESC']; break;
      default: $order = null;
    }

    $this->model->page = (isset($q['page']) ? (int) $q['page'] : 0);
    $this->model->limit = (isset($q['limit']) ? (int) $q['limit'] : self::PAGINATION_LIMIT_DEF);

    if ($this->model->page < 1) $this->model->page = 1;
    if ($this->model->limit < self::PAGINATION_LIMIT_MIN) $this->model->limit = self::PAGINATION_LIMIT_MIN;
    if ($this->model->limit > self::PAGINATION_LIMIT_MAX) $this->model->limit = self::PAGINATION_LIMIT_MAX;
    $this->model->pages = ceil(Event::getEventCount() / $this->model->limit);
    if ($this->model->page > $this->model->pages) $this->model->page = $this->model->pages;

    $this->model->events = Event::getAllEvents(
      null,
      $order,
      $this->model->limit,
      $this->model->limit * ( $this->model->page - 1 )
    );

    $this->model->sum_events = count($this->model->events);
    $this->model->_responseCode = 200;
    return true;
  }
}
