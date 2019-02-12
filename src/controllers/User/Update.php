<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\UserProfileNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\UserProfile;
use \BNETDocs\Models\User\Update as UserUpdateModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Update extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {
    $model = new UserUpdateModel();

    if ( !isset( Authentication::$user )) {

      $model->_responseCode = 401;

    } else {

      $model->_responseCode = 200;

      $conf = &Common::$config; // local variable for accessing config.
      $data = $router->getRequestBodyArray();

      // init model

      $model->username           = Authentication::$user->getUsername();
      $model->username_error     = [null, null];
      $model->username_max_len   =
        $conf->bnetdocs->user_register_requirements->username_length_max;

      $model->email_1            = Authentication::$user->getEmail();
      $model->email_2            = '';
      $model->email_error        = [null, null];

      $model->display_name       = Authentication::$user->getDisplayName();
      $model->display_name_error = [null, null];

      try {
        $model->profile = new UserProfile( Authentication::$user->getId() );
      } catch (UserProfileNotFoundException $e) {
        $model->profile = null;
      }

      if ( $model->profile ) {

        $model->biography = $model->profile->getBiography();

      }

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

        $model->biography = (
          isset($data['biography']) ? $data['biography'] : null
        );

        // process input

        if ($model->username !== Authentication::$user->getUsername()) {

          // username change request

          $req = &Common::$config->bnetdocs->user_register_requirements;

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

            if (!Authentication::$user->changeUsername( $model->username )) {
              $model->username_error = ['red', 'CHANGE_FAILED'];
            } else {
              $model->username_error = ['green', 'CHANGE_SUCCESS'];
            }

          }

        }

        if ($model->email_1 !== Authentication::$user->getEmail()) {

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

            if (!Authentication::$user->changeEmail( $model->email_2 )) {
              $model->email_error = ['red', 'CHANGE_FAILED'];
            } else {
              $model->email_error = ['green', 'CHANGE_SUCCESS'];
            }

          }

        }

        $display_name = $model->display_name;

        if (empty($display_name) && !is_null($display_name)) {
          $display_name = null; // blank strings become typed null
          $new_name = Authentication::$user->getUsername();
        } else {
          $new_name = $display_name;
        }

        $display_name_diff = (
          Authentication::$user->getDisplayName() !== $display_name
        );

        if ($display_name_diff) {

          // display name change request

          if (!Authentication::$user->changeDisplayName($display_name)) {
            $model->display_name_error = ['red', 'CHANGE_FAILED'];
          } else {
            $model->display_name_error = ['green', 'CHANGE_SUCCESS', $new_name];
          }

        }

        $profile_changed = false;

        if ($model->biography !== $model->profile->getBiography()) {

          // biography change request

          $model->profile->setBiography($model->biography);
          $model->biography_error = ['green', 'CHANGE_SUCCESS'];
          $profile_changed = true;

        }

        if ($profile_changed) {
          $model->profile->save();
        }

        Logger::logEvent(
          EventTypes::USER_EDITED,
          Authentication::$user->getId(),
          getenv('REMOTE_ADDR'),
          json_encode([
            'username_error'     => $model->username_error,
            'email_error'        => $model->email_error,
            'display_name_error' => $model->display_name_error,
            'user_id'            => Authentication::$user->getId(),
            'username'           => $model->username,
            'email_1'            => $model->email_1,
            'email_2'            => $model->email_2,
            'display_name'       => $display_name,
            'profile_changed'    => $profile_changed,
            'biography'          => $model->biography,
          ])
        );

      }

    }

    $view->render($model);

    $model->_responseHeaders['Content-Type'] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;
  }

}
