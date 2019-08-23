<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Models\User\Activate as UserActivateModel;

use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Activate extends Controller {

  public function &run( Router &$router, View &$view, array &$args ) {

    $data = $router->getRequestQueryArray();

    $model = new UserActivateModel();

    $model->error   = 'INVALID_TOKEN';
    $model->token   = isset( $data[ 't' ]) ? $data[ 't' ] : null;
    $model->user_id = isset( $data[ 'u' ]) ? $data[ 'u' ] : null;

    if ( !is_null( $model->user_id )) {
      $model->user_id = (int) $model->user_id;
    }

    try {
      $model->user = new User( $model->user_id );
    } catch ( UserNotFoundException $ex ) {
      $model->user = null;
    }

    if ( $model->user ) {
      $model->user->invalidateVerificationToken();
      $user_token = $model->user->getVerificationToken();

      if ( $user_token === $model->token ) {
        if (!$model->user->setVerified()) {
          $model->error = 'INTERNAL_ERROR';
        } else {
          $model->error = false;
          Logger::logEvent(
            EventTypes::USER_VERIFIED,
            $model->user_id,
            getenv( 'REMOTE_ADDR' ),
            json_encode([ 'error' => $model->error ])
          );
        }
      }
    }

    $view->render( $model );

    $model->_responseCode = 200;
    $model->_responseHeaders[ 'Content-Type' ] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

}
