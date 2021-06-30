<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
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

class Update extends Controller
{
  public function &run(Router &$router, View &$view, array &$args)
  {
    $model = new UserUpdateModel();
    $model->active_user = Authentication::$user;

    if (!isset($model->active_user))
    {
      $model->_responseCode = 401;
    }
    else
    {
      $model->_responseCode = 200;

      $conf = &Common::$config; // local variable for accessing config.
      $data = $router->getRequestBodyArray();

      // init model

      $model->username = $model->active_user->getUsername();
      $model->username_error = [null, null];
      $model->username_max_len = $conf->bnetdocs->user_register_requirements->username_length_max;

      $model->email_1 = $model->active_user->getEmail();
      $model->email_2 = '';
      $model->email_error = [null, null];

      $model->display_name = $model->active_user->getDisplayName();
      $model->display_name_error = [null, null];

      try
      {
        $model->profile = new UserProfile($model->active_user->getId());
      }
      catch (UserProfileNotFoundException $e)
      {
        $model->profile = null;
      }

      if ($model->profile)
      {
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
      }
      else
      {
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
        $profile->user_id            = $model->active_user->getId();
        $profile->website            = $model->website;

        $model->profile = new UserProfile($profile);
      }

      // process request
      if ($router->getRequestMethod() == 'POST')
      {
        // replace model values with form input
        $model->username = $data['username'] ?? null;
        $model->email_1 = $data['email_1'] ?? null;
        $model->email_2 = $data['email_2'] ?? null;
        $model->display_name = $data['display_name'] ?? null;
        $model->biography = $data['biography'] ?? null;
        $model->discord_username = $data['discord_username'] ?? null;
        $model->facebook_username = $data['facebook_username'] ?? null;
        $model->github_username = $data['github_username'] ?? null;
        $model->instagram_username = $data['instagram_username'] ?? null;
        $model->phone = $data['phone'] ?? null;
        $model->reddit_username = $data['reddit_username'] ?? null;
        $model->skype_username = $data['skype_username'] ?? null;
        $model->steam_id = $data['steam_id'] ?? null;
        $model->twitter_username = $data['twitter_username'] ?? null;
        $model->website = $data['website'] ?? null;

        // process input
        $req = &Common::$config->bnetdocs->user_register_requirements;

        // username change request
        if ($model->username !== $model->active_user->getUsername())
        {
          $username_len = strlen($model->username);
          if (empty($model->username))
          {
            // username is empty
            $model->username_error = ['danger', 'EMPTY'];
          }
          else if (is_numeric($req->username_length_max) && $username_len > $req->username_length_max)
          {
            // username too long
            $model->username_error = ['danger', 'USERNAME_LONG'];
          }
          else if (is_numeric($req->username_length_min) && $username_len < $req->username_length_min)
          {
            // username too short
            $model->username_error = ['danger', 'USERNAME_SHORT'];
          }
          else
          {
            // initiate username change
            if (!$model->active_user->changeUsername($model->username))
            {
              $model->username_error = ['danger', 'CHANGE_FAILED'];
            }
            else
            {
              $model->username_error = ['success', 'CHANGE_SUCCESS'];
            }
          }
        }

        // email change request
        if ($model->email_1 !== $model->active_user->getEmail())
        {
          // email denylist check
          $email_not_allowed = false;
          if ($req->email_enable_denylist)
          {
            $email_denylist = &Common::$config->email->recipient_denylist_regexp;
            foreach ($email_denylist as $_bad_email)
            {
              if (preg_match($_bad_email, $model->email_1))
              {
                $email_not_allowed = true;
                break;
              }
            }
          }

          if (strtolower($model->email_1) !== strtolower($model->email_2))
          {
            // email mismatch
            $model->email_error = ['danger', 'MISMATCH'];
          }
          else if (empty($model->email_2))
          {
            // email is empty
            $model->email_error = ['danger', 'EMPTY'];
          }
          else if ($req->email_validate_quick && !filter_var($model->email_2, FILTER_VALIDATE_EMAIL))
          {
            // email is invalid; it doesn't meet RFC 822 requirements
            $model->email_error = ['danger', 'INVALID'];
          }
          else if ($email_not_allowed)
          {
            // email is not allowed; it matches a denylist regular expression
            $model->email_error = ['danger', 'NOT_ALLOWED'];
          }
          else
          {
            // initiate email change
            if (!$model->active_user->changeEmail($model->email_2))
            {
              $model->email_error = ['danger', 'CHANGE_FAILED'];
            }
            else
            {
              $model->email_error = ['success', 'CHANGE_SUCCESS'];
            }
          }
        }

        // display name change request
        $display_name = $model->display_name;
        if (empty($display_name) && !is_null($display_name))
        {
          $display_name = null; // blank strings become typed null
          $new_name = $model->active_user->getUsername();
        }
        else
        {
          $new_name = $display_name;
        }
        $display_name_diff = (
          $model->active_user->getDisplayName() !== $display_name
        );
        if ($display_name_diff)
        {
          if (!$model->active_user->changeDisplayName($display_name))
          {
            $model->display_name_error = ['danger', 'CHANGE_FAILED'];
          }
          else
          {
            $model->display_name_error = ['success', 'CHANGE_SUCCESS', $new_name];
          }
        }

        $profile_changed = false;

        // biography change request
        if ($model->biography !== $model->profile->getBiography())
        {
          if (strlen($model->biography) > $model->biography_max_len)
          {
            $model->biography_error = ['danger', 'TOO_LONG'];
          }
          else
          {
            $model->profile->setBiography($model->biography);
            $model->biography_error = ['success', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }
        }

        // discord username change request
        if ($model->discord_username !== $model->profile->getDiscordUsername())
        {
          if (strlen($model->discord_username) > $model->discord_username_max_len)
          {
            $model->discord_username_error = ['danger', 'TOO_LONG'];
          }
          else
          {
            $model->profile->setDiscordUsername($model->discord_username);
            $model->discord_username_error = ['success', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }
        }

        // facebook username change request
        if ($model->facebook_username !== $model->profile->getFacebookUsername())
        {
          if (strlen($model->facebook_username) > $model->facebook_username_max_len)
          {
            $model->facebook_username_error = ['danger', 'TOO_LONG'];
          }
          else
          {
            $model->profile->setFacebookUsername($model->facebook_username);
            $model->facebook_username_error = ['success', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }
        }

        // github username change request
        if ($model->github_username !== $model->profile->getGitHubUsername())
        {
          if (strlen($model->github_username) > $model->github_username_max_len)
          {
            $model->github_username_error = ['danger', 'TOO_LONG'];
          }
          else
          {
            $model->profile->setGitHubUsername($model->github_username);
            $model->github_username_error = ['success', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }
        }

        // instagram username change request
        if ($model->instagram_username !== $model->profile->getInstagramUsername())
        {
          if (strlen($model->instagram_username) > $model->instagram_username_max_len)
          {
            $model->instagram_username_error = ['danger', 'TOO_LONG'];
          }
          else
          {
            $model->profile->setInstagramUsername($model->instagram_username);
            $model->instagram_username_error = ['success', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }
        }

        // phone change request
        if ($model->phone !== $model->profile->getPhone())
        {
          if (strlen($model->phone) > $model->phone_max_len)
          {
            $model->phone_error = ['danger', 'TOO_LONG'];
          }
          else
          {
            $model->profile->setPhone($model->phone);
            $model->phone_error = ['success', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }
        }

        // reddit username change request
        if ($model->reddit_username !== $model->profile->getRedditUsername())
        {
          if (strlen($model->reddit_username) > $model->reddit_username_max_len)
          {
            $model->reddit_username_error = ['danger', 'TOO_LONG'];
          }
          else
          {
            $model->profile->setRedditUsername($model->reddit_username);
            $model->reddit_username_error = ['success', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }
        }

        // skype username change request
        if ($model->skype_username !== $model->profile->getSkypeUsername())
        {
          if (strlen($model->skype_username) > $model->skype_username_max_len)
          {
            $model->skype_username_error = ['danger', 'TOO_LONG'];
          }
          else
          {
            $model->profile->setSkypeUsername($model->skype_username);
            $model->skype_username_error = ['success', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }
        }

        // steam id change request
        if ($model->steam_id !== $model->profile->getSteamId())
        {
          if (strlen($model->steam_id) > $model->steam_id_max_len)
          {
            $model->steam_id_error = ['danger', 'TOO_LONG'];
          }
          else
          {
            $model->profile->setSteamId($model->steam_id);
            $model->steam_id_error = ['success', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }
        }

        // twitter username change request
        if ($model->twitter_username !== $model->profile->getTwitterUsername())
        {
          if (strlen($model->twitter_username) > $model->twitter_username_max_len)
          {
            $model->twitter_username_error = ['danger', 'TOO_LONG'];
          }
          else
          {
            $model->profile->setTwitterUsername($model->twitter_username);
            $model->twitter_username_error = ['success', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }
        }

        // website change request
        if ($model->website !== $model->profile->getWebsite(false))
        {
          if (strlen($model->website) > $model->website_max_len)
          {
            $model->website_error = ['danger', 'TOO_LONG'];
          }
          else
          {
            $model->profile->setWebsite($model->website);
            $model->website_error = ['success', 'CHANGE_SUCCESS'];
            $profile_changed = true;
          }
        }

        if ($profile_changed)
        {
          $model->profile->save();
        }

        Logger::logEvent(
          EventTypes::USER_EDITED,
          $model->active_user->getId(),
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
            'user_id'                  => $model->active_user->getId(),
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
