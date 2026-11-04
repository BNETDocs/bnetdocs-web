<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\Core\Config;
use \BNETDocs\Libraries\Core\Recaptcha;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\EventLog\EventTypes;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\User\User;
use \BNETDocs\Libraries\User\VerificationEmail;
use \BNETDocs\Models\User\Register as RegisterModel;

class Register extends \BNETDocs\Controllers\Base
{
  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new RegisterModel();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args): bool
  {
    $this->model->recaptcha = new Recaptcha(
      Config::get('recaptcha.secret'),
      Config::get('recaptcha.sitekey'),
      Config::get('recaptcha.url')
    );
    $this->model->username_max_len = Config::get('bnetdocs.user_register_requirements.username_length_max') ?? User::MAX_USERNAME;

    if (Config::get('bnetdocs.user_register_disabled'))
      $this->model->error = RegisterModel::ERROR_REGISTER_DISABLED;
    else if (Router::requestMethod() == 'POST')
      $this->tryRegister();

    $this->model->_responseCode = \BNETDocs\Libraries\Core\HttpCode::HTTP_OK;
    return true;
  }

  protected function tryRegister(): void
  {
    if (Config::get('bnetdocs.user_register_disabled'))
    {
      $this->model->error = RegisterModel::ERROR_REGISTER_DISABLED;
      return;
    }

    if (!is_null($this->model->active_user))
    {
      $this->model->error = RegisterModel::ERROR_ALREADY_LOGGED_IN;
      return;
    }

    $q = Router::query();
    $this->model->email = $q['email'] ?? null;
    $this->model->username = $q['username'] ?? null;

    $this->model->honeypot = $q['confirm_email'] ?? null;
    if (is_string($this->model->honeypot) && strlen($this->model->honeypot) > 0)
    {
      // This field should never be filled unless it was a bot.
      $this->model->error = RegisterModel::ERROR_REGISTER_DISABLED;
      return;
    }

    $email = $this->model->email;
    $username = $this->model->username;
    $pw1 = $q['pw1'] ?? null;
    $pw2 = $q['pw2'] ?? null;

    $captcha = $q['g-recaptcha-response'] ?? null;
    try
    {
      if (!$this->model->recaptcha->verify($captcha, getenv('REMOTE_ADDR')))
      {
        $this->model->error = RegisterModel::ERROR_INVALID_CAPTCHA;
        return;
      }
    }
    catch (\BNETDocs\Exceptions\RecaptchaException)
    {
      $this->model->error = RegisterModel::ERROR_INVALID_CAPTCHA;
      return;
    }

    if ($pw1 !== $pw2)
    {
      $this->model->error = RegisterModel::ERROR_NONMATCHING_PASSWORD;
      return;
    }

    $pwlen = strlen($pw1);
    $usernamelen = strlen($username);
    $req = Config::get('bnetdocs.user_register_requirements') ?? [];
    $email_denylist = Config::get('email.recipient_denylist_regexp') ?? [];
    $countrycode_denylist = $req['geoip_countrycode_denylist'] ?? null;

    if (($req['email_validate_quick'] ?? null) && !filter_var($email, FILTER_VALIDATE_EMAIL))
    {
      $this->model->error = RegisterModel::ERROR_INVALID_EMAIL;
      return;
    }

    if ($req['email_enable_denylist'] ?? null)
    {
      foreach ($email_denylist as $_bad_email)
      {
        if (preg_match($_bad_email, $email))
        {
          $this->model->error = RegisterModel::ERROR_EMAIL_NOT_ALLOWED;
          return;
        }
      }
    }

    if (!($req['password_allow_email'] ?? null) && stripos($pw1, $email))
    {
      $this->model->error = RegisterModel::ERROR_PASSWORD_CONTAINS_EMAIL;
      return;
    }

    if (!($req['password_allow_username'] ?? null) && stripos($pw1, $username))
    {
      $this->model->error = RegisterModel::ERROR_PASSWORD_CONTAINS_USERNAME;
      return;
    }

    if (is_numeric($req['username_length_max'] ?? User::MAX_USERNAME)
      && $usernamelen > ($req['username_length_max'] ?? User::MAX_USERNAME))
    {
      $this->model->error = RegisterModel::ERROR_USERNAME_TOO_LONG;
      return;
    }

    if (is_numeric($req['username_length_min'] ?? 3) && $usernamelen < ($req['username_length_min'] ?? 3))
    {
      $this->model->error = RegisterModel::ERROR_USERNAME_TOO_SHORT;
      return;
    }

    if (is_numeric($req['password_length_max'] ?? User::MAX_PASSWORD_HASH)
      && $pwlen > ($req['password_length_max'] ?? User::MAX_PASSWORD_HASH))
    {
      $this->model->error = RegisterModel::ERROR_PASSWORD_TOO_LONG;
      return;
    }

    if (is_numeric($req['password_length_min'] ?? 4) && $pwlen < ($req['password_length_min'] ?? 4))
    {
      $this->model->error = RegisterModel::ERROR_PASSWORD_TOO_SHORT;
      return;
    }

    $denylist = Config::get('bnetdocs.user_password_denylist_map') ?? '../etc/password_denylist.json';
    $denylist = json_decode(file_get_contents('./' . $denylist));
    if ($denylist)
    {
      foreach ($denylist as $denylist_pw)
      {
        if (strtolower($denylist_pw->password) == strtolower($pw1))
        {
          $this->model->error = RegisterModel::ERROR_PASSWORD_DENYLIST;
          $this->model->denylist_reason = $denylist_pw->reason;
          return;
        }
      }
    }

    $their_country = \BNETDocs\Libraries\Core\GeoIP::getCountryISOCode(getenv('REMOTE_ADDR'));
    if ($their_country)
    {
      foreach ($countrycode_denylist as $bad_country => $reason)
      {
        if (strtoupper($their_country) == strtoupper($bad_country))
        {
          $this->model->error = RegisterModel::ERROR_COUNTRY_DENIED;
          $this->model->denylist_reason = $reason;
          return;
        }
      }
    }

    try
    {
      if (User::findIdByEmail($email))
      {
        $this->model->error = RegisterModel::ERROR_EMAIL_ALREADY_USED;
        return;
      }
    } catch (UserNotFoundException) {}

    try
    {
      if (User::findIdByUsername($username))
      {
        $this->model->error = RegisterModel::ERROR_USERNAME_TAKEN;
        return;
      }
    } catch (UserNotFoundException) {}

    $user = new User(null);
    $user->setEmail($email);
    $user->setPassword($pw1);
    $user->setUsername($username);
    $user->setVerified(false, true);
    $this->model->error = $user->commit() ? false : RegisterModel::ERROR_INTERNAL;
    $user_id = $user->getId();

    if (!is_null($user_id))
    {
      $event = Logger::initEvent(
        EventTypes::USER_CREATED,
        $user_id,
        getenv('REMOTE_ADDR'),
        [
          'error'           => $this->model->error,
          'denylist_reason' => $this->model->denylist_reason,
          'requirements'    => $req,
          'email'           => $email,
          'username'        => $username,
          'display_name'    => null,
          'options_bitmask' => 0,
        ]
      );

      if ($event->commit())
      {
        $embed = Logger::initDiscordEmbed($event, $user->getURI());
        Logger::logToDiscord($event, $embed);
      }

      try
      {
        VerificationEmail::send($user, $user_id);
      }
      catch (\Throwable)
      {
        $this->model->error = RegisterModel::ERROR_EMAIL_FAILURE;
      }
    }
  }
}
