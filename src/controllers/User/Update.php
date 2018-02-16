<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\Update as UserUpdateModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Update extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UserUpdateModel();

    $user_id = (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
    $user    = (!is_null($user_id) ? new User($user_id) : null);

    if ( is_null( $user ) ) {

      $model->_responseCode = 401;

    } else {

      $model->_responseCode = 200;

      $data = $router->getRequestBodyArray();

      // init model

      $model->username           = $user->getUsername();
      $model->username_error     = [null, null];

      $model->email_1            = $user->getEmail();
      $model->email_2            = '';
      $model->email_error        = [null, null];

      $model->display_name       = $user->getDisplayName();
      $model->display_name_error = [null, null];

      // process request

      if ($router->getRequestMethod() == 'POST') {

        // replace model values with form input

        $model->username = (
          isset($data['username']) ? $data['username'] : null
        );

        $model->email_1 = (
          isset($data['email_1']) ? $data['email_1'] : null
        );

        $model->email_2 = (
          isset($data['email_2']) ? $data['email_2'] : null
        );

        $model->display_name = (
          isset($data['display_name']) ? $data['display_name'] : null
        );

        // process input

        if ($model->username !== $user->getUsername()) {

          // username change request

          $req = Common::$config->bnetdocs->user_register_requirements;

          $username_len = strlen($model->username);

          if (empty($model->username)) {

            // username is empty
            $model->username_error = ['red', 'EMPTY'];

          } else if (is_numeric($req->username_length_max)
            && $username_len > $req->username_length_max) {

            // username too long
            $model->username_error = ['red', 'USERNAME_LONG'];

          } else if (is_numeric($req->username_length_min)
            && $username_len < $req->username_length_min) {

            // username too short
            $model->username_error = ['red', 'USERNAME_SHORT'];

          } else {

            // initiate username change

            if (!$user->changeUsername( $model->username )) {
              $model->username_error = ['red', 'CHANGE_FAILED'];
            } else {
              $model->username_error = ['green', 'CHANGE_SUCCESS'];
            }

          }

        }

        if ($model->email_1 !== $user->getEmail()) {

          // email change request

          if (strtolower($model->email_1) !== strtolower($model->email_2)) {

            // email mismatch
            $model->email_error = ['red', 'MISMATCH'];

          } else if (empty($model->email_2)) {

            // email is empty
            $model->email_error = ['red', 'EMPTY'];

          } else if (!filter_var($model->email_2, FILTER_VALIDATE_EMAIL)) {

            // email is invalid; it doesn't meet RFC 822 requirements
            $model->email_error = ['red', 'INVALID'];

          } else {

            // initiate email change

            if (!$user->changeEmail( $model->email_2 )) {
              $model->email_error = ['red', 'CHANGE_FAILED'];
            } else {
              $model->email_error = ['green', 'CHANGE_SUCCESS'];
            }

          }

        }

        if (!empty($model->display_name)) {

          // display name change request

          $model->display_name_error = ['red', 'CHANGE_FAILED'];

        }

      }

    }

    $view->render($model);

    $model->_responseHeaders['Content-Type'] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;
  }

}
