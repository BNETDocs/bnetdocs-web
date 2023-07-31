<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\UserProfile;
use \CarlBennett\MVC\Libraries\Common;
use \StdClass;
use \Throwable;
use \UnexpectedValueException;

class Update extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\User\Update();
  }

  public function invoke(?array $args): bool
  {
    if (!isset($this->model->active_user))
    {
      $this->model->_responseCode = 401;
      return true;
    }

    $this->model->_responseCode = 200;

    $conf = &Common::$config; // local variable for accessing config.

    $id = Router::query()['id'] ?? null;
    if (!is_null($id) && strlen($id) > 0 && $this->model->active_user->getOption(User::OPTION_ACL_USER_MODIFY))
    {
      try { $this->model->user = new User($id); }
      catch (UnexpectedValueException) { $this->model->user = null; }
    }
    if (!$this->model->user)
    {
      $id = $this->model->active_user->getId();
      $this->model->user = $this->model->active_user;
    }

    // init model

    $this->model->username = $this->model->user->getUsername();
    $this->model->username_error = [null, null];
    $this->model->username_max_len = $conf->bnetdocs->user_register_requirements->username_length_max;

    $this->model->email_1 = $this->model->user->getEmail();
    $this->model->email_2 = '';
    $this->model->email_error = [null, null];

    $this->model->display_name = $this->model->user->getDisplayName();
    $this->model->display_name_error = [null, null];

    try { $this->model->profile = new UserProfile($id); }
    catch (\BNETDocs\Exceptions\UserProfileNotFoundException) { $this->model->profile = null; }

    if ($this->model->profile)
    {
      $this->model->biography          = $this->model->profile->getBiography();
      $this->model->discord_username   = $this->model->profile->getDiscordUsername();
      $this->model->facebook_username  = $this->model->profile->getFacebookUsername();
      $this->model->github_username    = $this->model->profile->getGitHubUsername();
      $this->model->instagram_username = $this->model->profile->getInstagramUsername();
      $this->model->phone              = $this->model->profile->getPhone();
      $this->model->reddit_username    = $this->model->profile->getRedditUsername();
      $this->model->skype_username     = $this->model->profile->getSkypeUsername();
      $this->model->steam_id           = $this->model->profile->getSteamId();
      $this->model->twitter_username   = $this->model->profile->getTwitterUsername();
      $this->model->website            = $this->model->profile->getWebsite(false);
    }
    else
    {
      $profile = new StdClass();

      $profile->biography          = $this->model->biography;
      $profile->discord_username   = $this->model->discord_username;
      $profile->facebook_username  = $this->model->facebook_username;
      $profile->github_username    = $this->model->github_username;
      $profile->instagram_username = $this->model->instagram_username;
      $profile->phone              = $this->model->phone;
      $profile->reddit_username    = $this->model->reddit_username;
      $profile->skype_username     = $this->model->skype_username;
      $profile->steam_id           = $this->model->steam_id;
      $profile->twitter_username   = $this->model->twitter_username;
      $profile->user_id            = $this->model->user->getId();
      $profile->website            = $this->model->website;

      $this->model->profile = new UserProfile($profile);
    }

    // process request
    if (Router::requestMethod() == Router::METHOD_POST)
    {
      // replace model values with form input
      $this->model->biography = $data['biography'] ?? null;
      $this->model->discord_username = $data['discord_username'] ?? null;
      $this->model->display_name = $data['display_name'] ?? null;
      $this->model->email_1 = $data['email_1'] ?? null;
      $this->model->email_2 = $data['email_2'] ?? null;
      $this->model->facebook_username = $data['facebook_username'] ?? null;
      $this->model->github_username = $data['github_username'] ?? null;
      $this->model->instagram_username = $data['instagram_username'] ?? null;
      $this->model->phone = $data['phone'] ?? null;
      $this->model->reddit_username = $data['reddit_username'] ?? null;
      $this->model->skype_username = $data['skype_username'] ?? null;
      $this->model->steam_id = $data['steam_id'] ?? null;
      $this->model->twitter_username = $data['twitter_username'] ?? null;
      $this->model->username = $data['username'] ?? null;
      $this->model->website = $data['website'] ?? null;

      // convert empty strings to null
      if (empty($this->model->biography)) $this->model->biography = null;
      if (empty($this->model->discord_username)) $this->model->discord_username = null;
      if (empty($this->model->display_name)) $this->model->display_name = null;
      if (empty($this->model->email_1)) $this->model->email_1 = null;
      if (empty($this->model->email_2)) $this->model->email_2 = null;
      if (empty($this->model->facebook_username)) $this->model->facebook_username = null;
      if (empty($this->model->github_username)) $this->model->github_username = null;
      if (empty($this->model->instagram_username)) $this->model->instagram_username = null;
      if (empty($this->model->phone)) $this->model->phone = null;
      if (empty($this->model->reddit_username)) $this->model->reddit_username = null;
      if (empty($this->model->skype_username)) $this->model->skype_username = null;
      if (empty($this->model->steam_id)) $this->model->steam_id = null;
      if (empty($this->model->twitter_username)) $this->model->twitter_username = null;
      if (empty($this->model->username)) $this->model->username = null;
      if (empty($this->model->website)) $this->model->website = null;

      // get user account restrictions
      $req = &Common::$config->bnetdocs->user_register_requirements;

      // username change request
      if ($this->model->username !== $this->model->user->getUsername())
      {
        $username_len = strlen($this->model->username);
        if (empty($this->model->username))
        {
          // username is empty
          $this->model->username_error = ['danger', 'EMPTY'];
        }
        else if (is_numeric($req->username_length_max) && $username_len > $req->username_length_max)
        {
          // username too long
          $this->model->username_error = ['danger', 'USERNAME_LONG'];
        }
        else if (is_numeric($req->username_length_min) && $username_len < $req->username_length_min)
        {
          // username too short
          $this->model->username_error = ['danger', 'USERNAME_SHORT'];
        }
        else
        {
          // initiate username change
          try
          {
            $this->model->user->setUsername($this->model->username);
            $this->model->user->commit();
            $this->model->username_error = ['success', 'CHANGE_SUCCESS'];
          }
          catch (Throwable $e)
          {
            $this->model->username_error = ['danger', 'CHANGE_FAILED'];
            throw $e;
          }
        }
      }

      // email change request
      if ($this->model->email_1 !== $this->model->user->getEmail())
      {
        // email denylist check
        $email_not_allowed = false;
        if ($req->email_enable_denylist)
        {
          $email_denylist = &Common::$config->email->recipient_denylist_regexp;
          foreach ($email_denylist as $_bad_email)
          {
            if (preg_match($_bad_email, $this->model->email_1))
            {
              $email_not_allowed = true;
              break;
            }
          }
        }

        if (strtolower($this->model->email_1) !== strtolower($this->model->email_2))
        {
          // email mismatch
          $this->model->email_error = ['danger', 'MISMATCH'];
        }
        else if (empty($this->model->email_2))
        {
          // email is empty
          $this->model->email_error = ['danger', 'EMPTY'];
        }
        else if ($req->email_validate_quick && !filter_var($this->model->email_2, FILTER_VALIDATE_EMAIL))
        {
          // email is invalid; it doesn't meet RFC 822 requirements
          $this->model->email_error = ['danger', 'INVALID'];
        }
        else if ($email_not_allowed)
        {
          // email is not allowed; it matches a denylist regular expression
          $this->model->email_error = ['danger', 'NOT_ALLOWED'];
        }
        else
        {
          // initiate email change
          try
          {
            $this->model->user->setEmail($this->model->email_2);
            $this->model->user->commit();
            $this->model->email_error = ['success', 'CHANGE_SUCCESS'];
          }
          catch (Throwable $e)
          {
            $this->model->email_error = ['danger', 'CHANGE_FAILED'];
            throw $e;
          }
        }
      }

      // display name change request
      $display_name = $this->model->display_name;
      if (empty($display_name) && !is_null($display_name))
      {
        $display_name = null; // blank strings become typed null
        $new_name = $this->model->user->getUsername();
      }
      else
      {
        $new_name = $display_name;
      }
      if ($this->model->user->getDisplayName() !== $display_name)
      {
        try
        {
          $this->model->user->setDisplayName($display_name);
          $this->model->user->commit();
          $this->model->display_name_error = ['success', 'CHANGE_SUCCESS', $new_name];
        }
        catch (Throwable $e)
        {
          $this->model->display_name_error = ['danger', 'CHANGE_FAILED'];
          throw $e;
        }
      }

      $profile_changed = false;

      // biography change request
      if ($this->model->biography !== $this->model->profile->getBiography())
      {
        if (strlen($this->model->biography) > $this->model->biography_max_len)
        {
          $this->model->biography_error = ['danger', 'TOO_LONG'];
        }
        else
        {
          $this->model->profile->setBiography($this->model->biography);
          $this->model->biography_error = ['success', 'CHANGE_SUCCESS'];
          $profile_changed = true;
        }
      }

      // discord username change request
      if ($this->model->discord_username !== $this->model->profile->getDiscordUsername())
      {
        if (strlen($this->model->discord_username) > $this->model->discord_username_max_len)
        {
          $this->model->discord_username_error = ['danger', 'TOO_LONG'];
        }
        else
        {
          $this->model->profile->setDiscordUsername($this->model->discord_username);
          $this->model->discord_username_error = ['success', 'CHANGE_SUCCESS'];
          $profile_changed = true;
        }
      }

      // facebook username change request
      if ($this->model->facebook_username !== $this->model->profile->getFacebookUsername())
      {
        if (strlen($this->model->facebook_username) > $this->model->facebook_username_max_len)
        {
          $this->model->facebook_username_error = ['danger', 'TOO_LONG'];
        }
        else
        {
          $this->model->profile->setFacebookUsername($this->model->facebook_username);
          $this->model->facebook_username_error = ['success', 'CHANGE_SUCCESS'];
          $profile_changed = true;
        }
      }

      // github username change request
      if ($this->model->github_username !== $this->model->profile->getGitHubUsername())
      {
        if (strlen($this->model->github_username) > $this->model->github_username_max_len)
        {
          $this->model->github_username_error = ['danger', 'TOO_LONG'];
        }
        else
        {
          $this->model->profile->setGitHubUsername($this->model->github_username);
          $this->model->github_username_error = ['success', 'CHANGE_SUCCESS'];
          $profile_changed = true;
        }
      }

      // instagram username change request
      if ($this->model->instagram_username !== $this->model->profile->getInstagramUsername())
      {
        if (strlen($this->model->instagram_username) > $this->model->instagram_username_max_len)
        {
          $this->model->instagram_username_error = ['danger', 'TOO_LONG'];
        }
        else
        {
          $this->model->profile->setInstagramUsername($this->model->instagram_username);
          $this->model->instagram_username_error = ['success', 'CHANGE_SUCCESS'];
          $profile_changed = true;
        }
      }

      // phone change request
      if ($this->model->phone !== $this->model->profile->getPhone())
      {
        if (strlen($this->model->phone) > $this->model->phone_max_len)
        {
          $this->model->phone_error = ['danger', 'TOO_LONG'];
        }
        else
        {
          $this->model->profile->setPhone($this->model->phone);
          $this->model->phone_error = ['success', 'CHANGE_SUCCESS'];
          $profile_changed = true;
        }
      }

      // reddit username change request
      if ($this->model->reddit_username !== $this->model->profile->getRedditUsername())
      {
        if (strlen($this->model->reddit_username) > $this->model->reddit_username_max_len)
        {
          $this->model->reddit_username_error = ['danger', 'TOO_LONG'];
        }
        else
        {
          $this->model->profile->setRedditUsername($this->model->reddit_username);
          $this->model->reddit_username_error = ['success', 'CHANGE_SUCCESS'];
          $profile_changed = true;
        }
      }

      // skype username change request
      if ($this->model->skype_username !== $this->model->profile->getSkypeUsername())
      {
        if (strlen($this->model->skype_username) > $this->model->skype_username_max_len)
        {
          $this->model->skype_username_error = ['danger', 'TOO_LONG'];
        }
        else
        {
          $this->model->profile->setSkypeUsername($this->model->skype_username);
          $this->model->skype_username_error = ['success', 'CHANGE_SUCCESS'];
          $profile_changed = true;
        }
      }

      // steam id change request
      if ($this->model->steam_id !== $this->model->profile->getSteamId())
      {
        if (strlen($this->model->steam_id) > $this->model->steam_id_max_len)
        {
          $this->model->steam_id_error = ['danger', 'TOO_LONG'];
        }
        else
        {
          $this->model->profile->setSteamId($this->model->steam_id);
          $this->model->steam_id_error = ['success', 'CHANGE_SUCCESS'];
          $profile_changed = true;
        }
      }

      // twitter username change request
      if ($this->model->twitter_username !== $this->model->profile->getTwitterUsername())
      {
        if (strlen($this->model->twitter_username) > $this->model->twitter_username_max_len)
        {
          $this->model->twitter_username_error = ['danger', 'TOO_LONG'];
        }
        else
        {
          $this->model->profile->setTwitterUsername($this->model->twitter_username);
          $this->model->twitter_username_error = ['success', 'CHANGE_SUCCESS'];
          $profile_changed = true;
        }
      }

      // website change request
      if ($this->model->website !== $this->model->profile->getWebsite(false))
      {
        if (strlen($this->model->website) > $this->model->website_max_len)
        {
          $this->model->website_error = ['danger', 'TOO_LONG'];
        }
        else
        {
          $this->model->profile->setWebsite($this->model->website);
          $this->model->website_error = ['success', 'CHANGE_SUCCESS'];
          $profile_changed = true;
        }
      }

      if ($profile_changed && $this->model->profile->commit())
      {
        $event = Logger::initEvent(
          \BNETDocs\Libraries\EventLog\EventTypes::USER_EDITED,
          $this->model->active_user,
          getenv('REMOTE_ADDR'),
          [
            'username_error'           => $this->model->username_error,
            'email_error'              => $this->model->email_error,
            'display_name_error'       => $this->model->display_name_error,
            'biography_error'          => $this->model->biography_error,
            'discord_username_error'   => $this->model->discord_username_error,
            'facebook_username_error'  => $this->model->facebook_username_error,
            'github_username_error'    => $this->model->github_username_error,
            'instagram_username_error' => $this->model->instagram_username_error,
            'phone_error'              => $this->model->phone_error,
            'reddit_username_error'    => $this->model->reddit_username_error,
            'skype_username_error'     => $this->model->skype_username_error,
            'steam_id_error'           => $this->model->steam_id_error,
            'twitter_username_error'   => $this->model->twitter_username_error,
            'website_error'            => $this->model->website_error,
            'user_id'                  => $this->model->user->getId(),
            'username'                 => $this->model->username,
            'email_1'                  => $this->model->email_1,
            'email_2'                  => $this->model->email_2,
            'display_name'             => $display_name,
            'profile_changed'          => $profile_changed,
            'biography'                => $this->model->biography,
            'discord_username'         => $this->model->discord_username,
            'facebook_username'        => $this->model->facebook_username,
            'github_username'          => $this->model->github_username,
            'instagram_username'       => $this->model->instagram_username,
            'phone'                    => $this->model->phone,
            'reddit_username'          => $this->model->reddit_username,
            'skype_username'           => $this->model->skype_username,
            'steam_id'                 => $this->model->steam_id,
            'twitter_username'         => $this->model->twitter_username,
            'website'                  => $this->model->website,
          ]
        );

        if ($event->commit())
        {
          $embed = Logger::initDiscordEmbed($event, $this->model->active_user->getURI(), [
            'Edited by' => $this->model->active_user->getAsMarkdown(),
            'Edited user' => $this->model->user->getAsMarkdown(),
          ]);
          Logger::logToDiscord($event, $embed);
        }
      }
    }

    return true;
  }
}
