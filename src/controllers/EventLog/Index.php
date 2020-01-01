<?php

namespace BNETDocs\Controllers\EventLog;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\Event;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\EventLog\Index as EventLogIndexModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Index extends Controller {

  const PAGINATION_LIMIT_DEF = 15;  // The default amount of items per page.
  const PAGINATION_LIMIT_MIN = 5;   // The least amount of items per page.
  const PAGINATION_LIMIT_MAX = 250; // The most amount of items per page.

  public function &run(Router &$router, View &$view, array &$args) {
    $model = new EventLogIndexModel();

    $model->user = Authentication::$user;

    $model->acl_allowed = ($model->user && $model->user->getAcl(
      User::OPTION_ACL_EVENT_LOG_VIEW
    ));

    if ($model->acl_allowed) {

      $query = $router->getRequestQueryArray();

      $model->order = (
        isset($query['order']) ? $query['order'] : 'datetime-desc'
      );

      switch ($model->order) {
        case 'id-asc':
          $order = ['id','ASC']; break;
        case 'id-desc':
          $order = ['id','DESC']; break;
        case 'datetime-asc':
          $order = ['event_datetime','ASC']; break;
        case 'datetime-desc':
          $order = ['event_datetime','DESC']; break;
        default:
          $order = null;
      }

      $model->page = (isset($query['page']) ? (int) $query['page'] : 0);

      $model->limit = (
        isset($query['limit']) ?
        (int) $query['limit'] : self::PAGINATION_LIMIT_DEF
      );

      if ($model->page < 1) { $model->page = 1; }

      if ($model->limit < self::PAGINATION_LIMIT_MIN) {
        $model->limit = self::PAGINATION_LIMIT_MIN;
      }

      if ($model->limit > self::PAGINATION_LIMIT_MAX) {
        $model->limit = self::PAGINATION_LIMIT_MAX;
      }

      $model->pages = ceil(Event::getEventCount() / $model->limit);

      if ($model->page > $model->pages) { $model->page = $model->pages; }

      $model->events = Event::getAllEvents(
        null,
        $order,
        $model->limit,
        $model->limit * ( $model->page - 1 )
      );

      $model->sum_events = count($model->events);

    }

    $view->render($model);
    $model->_responseCode = ($model->acl_allowed ? 200 : 403);
    return $model;
  }
}
