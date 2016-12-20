<?php

namespace BNETDocs\Controllers\EventLog;

use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\EventLog\Index as EventLogIndexModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Index extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new EventLogIndexModel();

    $model->user = (
      isset($_SESSION['user_id']) ? new User($_SESSION['user_id']) : null
    );

    $model->acl_allowed = ($model->user && $model->user->getAcl(
      User::OPTION_ACL_EVENT_LOG_VIEW
    ));

    if ($model->acl_allowed) {
      $model->event_log     = Logger::getAllEvents();
      $model->sum_event_log = count($model->event_log);
    }

    $view->render($model);

    $model->_responseCode = ($model->acl_allowed ? 200 : 403);
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
