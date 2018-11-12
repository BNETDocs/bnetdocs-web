<?php

namespace BNETDocs\Controllers\Server;

use \BNETDocs\Libraries\Exceptions\ServerNotFoundException;
use \BNETDocs\Libraries\Server;

use \BNETDocs\Models\Server\UpdateJob as UpdateJobModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View as ViewLib;

use \DateTime;
use \DateTimeZone;

class UpdateJob extends Controller {

  public function &run( Router &$router, ViewLib &$view, array &$args ) {

    $model = new UpdateJobModel();
    $action = $router->getRequestMethod();

    if ( $action !== 'POST' ) {

      $model->_responseCode = 405;
      $model->_responseHeaders[ 'Allow' ] = 'POST';

    } else {

      $q = $router->getRequestBodyArray();

      $server_id = ( isset( $q[ 'id' ]) ? $q[ 'id' ] : null );

      $job_token = (
        isset( $q[ 'job_token' ]) ? $q[ 'job_token' ] : null
      );

      $status = (
        isset( $q[ 'status' ]) ? $q[ 'status' ] : null
      );

      if ( !is_null( $server_id )) $server_id = (int) $server_id;
      if ( !is_null( $status )) $status = (int) $status;

      $authenticated = (
        $job_token === Common::$config->bnetdocs->server_update_job_token
      );

      if ( !( is_int( $server_id ) && is_int( $status ))) {

        $model->_responseCode = 400;

      } else if ( !$authenticated ) {

        $model->_responseCode = 403;

      } else {

        try {
          $model->server = new Server( $server_id );
        } catch ( ServerNotFoundException $e ) {
          $model->server = null;
        }

        if ( !$model->server ) {

          $model->_responseCode = 404;

        } else {

          $model->old_status_bitmask = $model->server->getStatusBitmask();

          $model->server->setStatusBitmask( $status );

          if ( $model->server->save() ) {
            $model->_responseCode = 200;
          }

        }
      }
    }

    $view->render( $model );

    $model->_responseHeaders[ 'Content-Type' ] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
