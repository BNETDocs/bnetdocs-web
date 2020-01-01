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

use \StdClass;

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

        $model->biography          = $model->profile->getBiography();
        $model->discord_username   = $model->profile->getDiscordUsername();
        $model->facebook_username  = $model->profile->getFacebookUsername();
        $model->github_username    = $model->profile->getGitHubUsername();
        $model->instagram_username = $model->profile->getInstagramUsername();
        $model->phone              = $model->profile->getPhone();
        $model->reddit_username    = $model->profile->getRedditUsername();
        $model->skype_username     = $model->profile->getSkypeUsername();
        $model->steam_id           = $model->profile->getSteamId();
        $model->twitter_username   = $model->profile->getTwitterUsername();
        $model->website            = $model->profile->getWebsite(false);

      } else {

        $profile = new StdClass();

        $profile->biography          = $model->biography;
        $profile->discord_username   = $model->discord_username;
        $profile->facebook_username  = $model->facebook_username;
        $profile->github_username    = $model->github_username;
        $profile->instagram_username = $model->instagram_username;
        $profile->phone              = $model->phone;
        $profile->reddit_username    = $model->reddit_username;
        $profile->skype_username     = $model->skype_username;
        $profile->steam_id           = $model->steam_id;
        $profile->twitter_username   = $model->twitter_username;
        $profile->user_id            = Authentication::$user->getId();
        $profile->website            = $model->website;

        $model->profile = new UserProfile($profile);

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

        $model->discord_username = (
          isset($data['discord_username']) ? $data['discord_username'] : null
        );

        $model->facebook_username = (
          isset($data['facebook_username']) ? $data['facebook_username'] : null
        );

        $model->github_username = (
          isset($data['github_username']) ? $data['github_username'] : null
        );

        $model->instagram_username = (
          isset($data['instagram_username']) ?
          $data['instagram_username'] : null
        );

        $model->phone = (
          isset($data['phone']) ? $data['phone'] : null
        );

        $model->reddit_username = (
          isset($data['reddit_username']) ? $data['reddit_username'] : null
        );

        $model->skype_username = (
          isset($data['skype_username']) ? $data['skype_username'] : null
        );

        $model->steam_id = (
          isset($data['steam_id']) ? $data['steam_id'] : null
        );

        $model->twitter_username = (
          isset($data['twitter_username']) ? $data['twitter_username'] : null
        );

        $model->website = (
          isset($data['website']) ? $data['website'] : null
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

          if (strlen($model->biography) > $model->biography_max_len) {
            $model->biography_error = ['red', 'TOO_LONG'];
          } else {
            $model->profile->setBiography($model->biography);
            $model->biography_error = ['green', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }

        }

        if (
          $model->discord_username !== $model->profile->getDiscordUsername()
        ) {

          // discord username change request

          if (strlen($model->discord_username) >
            $model->discord_username_max_len
          ) {
            $model->discord_username_error = ['red', 'TOO_LONG'];
          } else {
            $model->profile->setDiscordUsername($model->discord_username);
            $model->discord_username_error = ['green', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }

        }

        if (
          $model->facebook_username !== $model->profile->getFacebookUsername()
        ) {

          // facebook username change request

          if (strlen($model->facebook_username) >
            $model->facebook_username_max_len
          ) {
            $model->facebook_username_error = ['red', 'TOO_LONG'];
          } else {
            $model->profile->setFacebookUsername($model->facebook_username);
            $model->facebook_username_error = ['green', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }

        }

        if (
          $model->github_username !== $model->profile->getGitHubUsername()
        ) {

          // github username change request

          if (strlen($model->github_username) >
            $model->github_username_max_len
          ) {
            $model->github_username_error = ['red', 'TOO_LONG'];
          } else {
            $model->profile->setGitHubUsername($model->github_username);
            $model->github_username_error = ['green', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }

        }

        if (
          $model->instagram_username !== $model->profile->getInstagramUsername()
        ) {

          // instagram username change request

          if (strlen($model->instagram_username) >
            $model->instagram_username_max_len
          ) {
            $model->instagram_username_error = ['red', 'TOO_LONG'];
          } else {
            $model->profile->setInstagramUsername($model->instagram_username);
            $model->instagram_username_error = ['green', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }

        }

        if ($model->phone !== $model->profile->getPhone()) {

          // phone change request

          if (strlen($model->phone) > $model->phone_max_len) {
            $model->phone_error = ['red', 'TOO_LONG'];
          } else {
            $model->profile->setPhone($model->phone);
            $model->phone_error = ['green', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }

        }

        if ($model->reddit_username !== $model->profile->getRedditUsername()) {

          // reddit username change request

          if (strlen($model->reddit_username) >
            $model->reddit_username_max_len
          ) {
            $model->reddit_username_error = ['red', 'TOO_LONG'];
          } else {
            $model->profile->setRedditUsername($model->reddit_username);
            $model->reddit_username_error = ['green', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }

        }

        if ($model->skype_username !== $model->profile->getSkypeUsername()) {

          // skype username change request

          if (strlen($model->skype_username) > $model->skype_username_max_len) {
            $model->skype_username_error = ['red', 'TOO_LONG'];
          } else {
            $model->profile->setSkypeUsername($model->skype_username);
            $model->skype_username_error = ['green', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }

        }

        if ($model->steam_id !== $model->profile->getSteamId()) {

          // steam id change request

          if (strlen($model->steam_id) > $model->steam_id_max_len) {
            $model->steam_id_error = ['red', 'TOO_LONG'];
          } else {
            $model->profile->setSteamId($model->steam_id);
            $model->steam_id_error = ['green', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }

        }

        if ($model->twitter_username !== $model->profile->getTwitterUsername()) {

          // twitter username change request

          if (strlen($model->twitter_username) >
            $model->twitter_username_max_len
          ) {
            $model->twitter_username_error = ['red', 'TOO_LONG'];
          } else {
            $model->profile->setTwitterUsername($model->twitter_username);
            $model->twitter_username_error = ['green', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }

        }

        if ($model->website !== $model->profile->getWebsite(false)) {

          // website change request

          if (strlen($model->website) > $model->website_max_len) {
            $model->website_error = ['red', 'TOO_LONG'];
          } else {
            $model->profile->setWebsite($model->website);
            $model->website_error = ['green', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }

        }

        if ($profile_changed) {
          $model->profile->save();
        }

        Logger::logEvent(
          EventTypes::USER_EDITED,
          Authentication::$user->getId(),
          getenv('REMOTE_ADDR'),
          json_encode([
            'username_error'           => $model->username_error,
            'email_error'              => $model->email_error,
            'display_name_error'       => $model->display_name_error,
            'biography_error'          => $model->biography_error,
            'discord_username_error'   => $model->discord_username_error,
            'facebook_username_error'  => $model->facebook_username_error,
            'github_username_error'    => $model->github_username_error,
            'instagram_username_error' => $model->instagram_username_error,
            'phone_error'              => $model->phone_error,
            'reddit_username_error'    => $model->reddit_username_error,
            'skype_username_error'     => $model->skype_username_error,
            'steam_id_error'           => $model->steam_id_error,
            'twitter_username_error'   => $model->twitter_username_error,
            'website_error'            => $model->website_error,
            'user_id'                  => Authentication::$user->getId(),
            'username'                 => $model->username,
            'email_1'                  => $model->email_1,
            'email_2'                  => $model->email_2,
            'display_name'             => $display_name,
            'profile_changed'          => $profile_changed,
            'biography'                => $model->biography,
            'discord_username'         => $model->discord_username,
            'facebook_username'        => $model->facebook_username,
            'github_username'          => $model->github_username,
            'instagram_username'       => $model->instagram_username,
            'phone'                    => $model->phone,
            'reddit_username'          => $model->reddit_username,
            'skype_username'           => $model->skype_username,
            'steam_id'                 => $model->steam_id,
            'twitter_username'         => $model->twitter_username,
            'website'                  => $model->website,
          ])
        );

      }

    }

    $view->render($model);
    return $model;
  }
}
