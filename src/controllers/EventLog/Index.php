<?php

namespace BNETDocs\Controllers\EventLog;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\EventLog\Index as EventLogIndexModel;
use \BNETDocs\Views\EventLog\IndexHtml as EventLogIndexHtmlView;
use \BNETDocs\Views\EventLog\IndexJSON as EventLogIndexJSONView;

class Index extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new EventLogIndexHtmlView();
      break;
      case "json":
        $view = new EventLogIndexJSONView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new EventLogIndexModel();

    $model->user_session = UserSession::load($router);
    $model->user         = (isset($model->user_session) ?
                            new User($model->user_session->user_id) : null);

    $model->acl_allowed = ($model->user &&
      $model->user->getOptionsBitmask() & User::OPTION_ACL_EVENT_LOG_VIEW
    );

    if ($model->acl_allowed) {
      $model->event_log     = Logger::getAllEvents();
      $model->sum_event_log = count($model->event_log);
    }

    ob_start();
    $view->render($model);
    $router->setResponseCode(($model->acl_allowed ? 200 : 403));
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

}
