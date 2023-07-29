<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Exceptions\RecaptchaException;
use \BNETDocs\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\EventLog\Event;
use \BNETDocs\Libraries\EventLog\EventTypes;
use \BNETDocs\Libraries\GeoIP;
use \BNETDocs\Libraries\Recaptcha;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\User;
use \CarlBennett\MVC\Libraries\Common;
use \PHPMailer\PHPMailer\PHPMailer;

class Register extends \BNETDocs\Controllers\Base
{
  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\User\Register();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args): bool
  {
    $conf = &Common::$config; // local variable for accessing config.

    $this->model->error = null;
    $this->model->recaptcha = new Recaptcha(
      $conf->recaptcha->secret,
      $conf->recaptcha->sitekey,
      $conf->recaptcha->url
    );
    $this->model->username_max_len = $conf->bnetdocs->user_register_requirements->username_length_max;

    if ($conf->bnetdocs->user_register_disabled)
      $this->model->error = 'REGISTER_DISABLED';
    else if (Router::requestMethod() == 'POST')
      $this->tryRegister();

    $this->model->_responseCode = 200;
    return true;
  }

  protected function tryRegister() : void
  {
    $data = Router::query();
    $this->model->email    = (isset($data['email'   ]) ? $data['email'   ] : null);
    $this->model->username = (isset($data['username']) ? $data['username'] : null);
    if (!is_null($this->model->active_user)) {
      $this->model->error = 'ALREADY_LOGGED_IN';
      return;
    }
    $email    = $this->model->email;
    $username = $this->model->username;
    $pw1      = (isset($data['pw1']) ? $data['pw1'] : null);
    $pw2      = (isset($data['pw2']) ? $data['pw2'] : null);
    $captcha  = (
      isset($data['g-recaptcha-response']) ?
      $data['g-recaptcha-response']        :
      null
    );
    try {
      if (!$this->model->recaptcha->verify($captcha, getenv('REMOTE_ADDR'))) {
        $this->model->error = 'INVALID_CAPTCHA';
        return;
      }
    } catch (RecaptchaException $e) {
      $this->model->error = 'INVALID_CAPTCHA';
      return;
    }
    if ($pw1 !== $pw2) {
      $this->model->error = 'NONMATCHING_PASSWORD';
      return;
    }
    $pwlen       = strlen($pw1);
    $usernamelen = strlen($username);
    $req = &Common::$config->bnetdocs->user_register_requirements;
    $email_denylist = &Common::$config->email->recipient_denylist_regexp;
    $countrycode_denylist = &$req->geoip_countrycode_denylist;
    if ($req->email_validate_quick
      && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $this->model->error = 'INVALID_EMAIL';
      return;
    }
    if ($req->email_enable_denylist) {
      foreach ($email_denylist as $_bad_email) {
        if (preg_match($_bad_email, $email)) {
          $this->model->error = 'EMAIL_NOT_ALLOWED';
          return;
        }
      }
    }
    if (!$req->password_allow_email && stripos($pw1, $email)) {
      $this->model->error = 'PASSWORD_CONTAINS_EMAIL';
      return;
    }
    if (!$req->password_allow_username && stripos($pw1, $username)) {
      $this->model->error = 'PASSWORD_CONTAINS_USERNAME';
      return;
    }
    if (is_numeric($req->username_length_max)
      && $usernamelen > $req->username_length_max) {
      $this->model->error = 'USERNAME_TOO_LONG';
      return;
    }
    if (is_numeric($req->username_length_min)
      && $usernamelen < $req->username_length_min) {
      $this->model->error = 'USERNAME_TOO_SHORT';
      return;
    }
    if (is_numeric($req->password_length_max)
      && $pwlen > $req->password_length_max) {
      $this->model->error = 'PASSWORD_TOO_LONG';
      return;
    }
    if (is_numeric($req->password_length_min)
      && $pwlen < $req->password_length_min) {
      $this->model->error = 'PASSWORD_TOO_SHORT';
      return;
    }
    $denylist = Common::$config->bnetdocs->user_password_denylist_map;
    $denylist = json_decode(file_get_contents('./' . $denylist));
    if ($denylist) {
      foreach ($denylist as $denylist_pw) {
        if (strtolower($denylist_pw->password) == strtolower($pw1)) {
          $this->model->error = 'PASSWORD_BLACKLIST';
          $this->model->error_extra = $denylist_pw->reason;
          return;
        }
      }
    }
    $geoip_record = GeoIP::getRecord(getenv('REMOTE_ADDR'));
    if ($geoip_record) {
      $their_country = $geoip_record->country->isoCode;
      foreach ($countrycode_denylist as $bad_country => $reason) {
        if (strtoupper($their_country) == strtoupper($bad_country)) {
          $this->model->error = 'COUNTRY_DENIED';
          $this->model->error_extra = $reason;
          return;
        }
      }
    }
    if (Common::$config->bnetdocs->user_register_disabled) {
      $this->model->error = 'REGISTER_DISABLED';
      return;
    }

    try {
      if (User::findIdByEmail($email)) {
        $this->model->error = 'EMAIL_ALREADY_USED';
        return;
      }
    } catch (UserNotFoundException $e) {}

    try {
      if (User::findIdByUsername($username)) {
        $this->model->error = 'USERNAME_TAKEN';
        return;
      }
    } catch (UserNotFoundException $e) {}

    $user = new User(null);
    $user->setEmail($email);
    $user->setPassword($pw1);
    $user->setUsername($username);
    $user->setVerified(false, true);
    $this->model->error = $user->commit() ? false : 'INTERNAL_ERROR';
    $user_id = $user->getId();

    if (!is_null($user_id))
    {
      Event::log(
        EventTypes::USER_CREATED,
        $user_id,
        getenv('REMOTE_ADDR'),
        [
          'error'           => $this->model->error,
          'error_extra'     => $this->model->error_extra,
          'requirements'    => $req,
          'email'           => $email,
          'username'        => $username,
          'display_name'    => null,
          'options_bitmask' => 0,
        ]
      );

      $mail = new PHPMailer(true); // true enables exceptions
      $mail_config = &Common::$config->email;

      $state = new \StdClass();
      $state->mail = &$mail;
      $state->name = $user->getName();
      $state->token = $user->getVerifierToken();
      $state->user_id = $user_id;

      try {
        //Server settings
        $mail->Timeout = 10; // default is 300 per RFC2821 $ 4.5.3.2
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = $mail_config->smtp_host;
        $mail->SMTPAuth   = !empty($mail_config->smtp_user);
        $mail->Username   = $mail_config->smtp_user;
        $mail->Password   = $mail_config->smtp_password;
        $mail->SMTPSecure = $mail_config->smtp_tls ? 'tls' : '';
        $mail->Port       = $mail_config->smtp_port;

        //Recipients
        if (isset($mail_config->recipient_from[0])) {
          $mail->setFrom(
            $mail_config->recipient_from[0],
            $mail_config->recipient_from[1]
          );
        }

        $mail->addAddress($email, $username);

        if (isset($mail_config->recipient_reply_to[0])) {
          $mail->addReplyTo(
            $mail_config->recipient_reply_to[0],
            $mail_config->recipient_reply_to[1]
          );
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Account Verification';
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        ob_start();
        (new Template($state, 'Email/User/Register.rich'))->render();
        $mail->Body = ob_get_clean();

        ob_start();
        (new Template($state, 'Email/User/Register.plain'))->render();
        $mail->AltBody = ob_get_clean();

        $mail->send();

        Event::log(
          EventTypes::EMAIL_SENT,
          $user_id,
          getenv('REMOTE_ADDR'),
          [
            'from' => $mail->From,
            'to' => $mail->getToAddresses(),
            'reply_to' => $mail->getReplyToAddresses(),
            'subject' => $mail->Subject,
            'content_type' => $mail->ContentType,
            'body' => $mail->Body,
            'alt_body' => $mail->AltBody,
          ]
        );

      } catch (\Throwable $e) {
        $this->model->error = 'EMAIL_FAILURE';
      }
    }
  }
}
