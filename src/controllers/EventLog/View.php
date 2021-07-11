<?php

namespace BNETDocs\Controllers\EventLog;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\Event;
use \BNETDocs\Libraries\Exceptions\EventNotFoundException;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\EventLog\View as EventLogViewModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View as ViewMVC;

class View extends Controller {
  public function &run( Router &$router, ViewMVC &$view, array &$args ) {
    $model = new EventLogViewModel();

    $model->user = Authentication::$user;

    $model->acl_allowed = ( $model->user && $model->user->getOption(
      User::OPTION_ACL_EVENT_LOG_VIEW
    ));

    if ($model->acl_allowed) {

      $query = $router->getRequestQueryArray();

      $model->event = null;
      $model->id    = ( isset($query[ 'id' ]) ? (int) $query[ 'id' ] : null );

      if ( !is_null( $model->id ) ) {
        try {
          $model->event = new Event( $model->id );
        } catch ( EventNotFoundException $e ) {
          $model->event = null;
        }
      }

    }

    $view->render( $model );

    $model->_responseCode = (
      $model->acl_allowed ? (
        $model->event ? 200 : 404
      ) : 403
    );

    return $model;
  }
}
