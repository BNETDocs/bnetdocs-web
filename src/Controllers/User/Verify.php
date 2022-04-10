<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\Verify as UserVerifyModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \Exception;
use \InvalidArgumentException;

class Verify extends Controller {
  public function &run( Router &$router, View &$view, array &$args ) {
    $data = $router->getRequestQueryArray();

    $model = new UserVerifyModel();

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
    } catch ( InvalidArgumentException $ex ) {
      $model->user = null;
    }

    if ( $model->user ) {
      $user_token = $model->user->getVerifierToken();

      if ( $user_token === $model->token ) {
        try {
          $model->user->setVerified(true);
          $model->user->commit();
          $model->error = false;
        } catch (Exception $e) {
          $model->error = 'INTERNAL_ERROR';
        }

        if (!$model->error) {
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
    return $model;
  }
}
