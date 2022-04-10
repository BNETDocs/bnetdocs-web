<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\RecaptchaException;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\GeoIP;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Recaptcha;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\Register as UserRegisterModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \PHPMailer\PHPMailer\Exception;
use \PHPMailer\PHPMailer\PHPMailer;

use \StdClass;

class Register extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $conf = &Common::$config; // local variable for accessing config.

    $model               = new UserRegisterModel();
    $model->error        = null;
    $model->recaptcha    = new Recaptcha(
      $conf->recaptcha->secret,
      $conf->recaptcha->sitekey,
      $conf->recaptcha->url
    );

    $model->username_max_len =
      $conf->bnetdocs->user_register_requirements->username_length_max;

    if (Common::$config->bnetdocs->user_register_disabled) {
      $model->error = 'REGISTER_DISABLED';
    } else if ($router->getRequestMethod() == 'POST') {
      $this->tryRegister($router, $model);
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }

  protected function tryRegister(Router &$router, UserRegisterModel &$model) {
    $data = $router->getRequestBodyArray();
    $model->email    = (isset($data['email'   ]) ? $data['email'   ] : null);
    $model->username = (isset($data['username']) ? $data['username'] : null);
    if ( isset( Authentication::$user )) {
      $model->error = 'ALREADY_LOGGED_IN';
      return;
    }
    $email    = $model->email;
    $username = $model->username;
    $pw1      = (isset($data['pw1']) ? $data['pw1'] : null);
    $pw2      = (isset($data['pw2']) ? $data['pw2'] : null);
    $captcha  = (
      isset($data['g-recaptcha-response']) ?
      $data['g-recaptcha-response']        :
      null
    );
    try {
      if (!$model->recaptcha->verify($captcha, getenv('REMOTE_ADDR'))) {
        $model->error = 'INVALID_CAPTCHA';
        return;
      }
    } catch (RecaptchaException $e) {
      $model->error = 'INVALID_CAPTCHA';
      return;
    }
    if ($pw1 !== $pw2) {
      $model->error = 'NONMATCHING_PASSWORD';
      return;
    }
    $pwlen       = strlen($pw1);
    $usernamelen = strlen($username);
    $req = &Common::$config->bnetdocs->user_register_requirements;
    $email_denylist = &Common::$config->email->recipient_denylist_regexp;
    $countrycode_denylist = &$req->geoip_countrycode_denylist;
    if ($req->email_validate_quick
      && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $model->error = 'INVALID_EMAIL';
      return;
    }
    if ($req->email_enable_denylist) {
      foreach ($email_denylist as $_bad_email) {
        if (preg_match($_bad_email, $email)) {
          $model->error = 'EMAIL_NOT_ALLOWED';
          return;
        }
      }
    }
    if (!$req->password_allow_email && stripos($pw1, $email)) {
      $model->error = 'PASSWORD_CONTAINS_EMAIL';
      return;
    }
    if (!$req->password_allow_username && stripos($pw1, $username)) {
      $model->error = 'PASSWORD_CONTAINS_USERNAME';
      return;
    }
    if (is_numeric($req->username_length_max)
      && $usernamelen > $req->username_length_max) {
      $model->error = 'USERNAME_TOO_LONG';
      return;
    }
    if (is_numeric($req->username_length_min)
      && $usernamelen < $req->username_length_min) {
      $model->error = 'USERNAME_TOO_SHORT';
      return;
    }
    if (is_numeric($req->password_length_max)
      && $pwlen > $req->password_length_max) {
      $model->error = 'PASSWORD_TOO_LONG';
      return;
    }
    if (is_numeric($req->password_length_min)
      && $pwlen < $req->password_length_min) {
      $model->error = 'PASSWORD_TOO_SHORT';
      return;
    }
    $denylist = Common::$config->bnetdocs->user_password_denylist_map;
    $denylist = json_decode(file_get_contents('./' . $denylist));
    if ($denylist) {
      foreach ($denylist as $denylist_pw) {
        if (strtolower($denylist_pw->password) == strtolower($pw1)) {
          $model->error = 'PASSWORD_BLACKLIST';
          $model->error_extra = $denylist_pw->reason;
          return;
        }
      }
    }
    $geoip_record = GeoIP::getRecord(getenv('REMOTE_ADDR'));
    if ($geoip_record) {
      $their_country = $geoip_record->country->isoCode;
      foreach ($countrycode_denylist as $bad_country => $reason) {
        if (strtoupper($their_country) == strtoupper($bad_country)) {
          $model->error = 'COUNTRY_DENIED';
          $model->error_extra = $reason;
          return;
        }
      }
    }
    if (Common::$config->bnetdocs->user_register_disabled) {
      $model->error = 'REGISTER_DISABLED';
      return;
    }

    try {
      if (User::findIdByEmail($email)) {
        $model->error = 'EMAIL_ALREADY_USED';
        return;
      }
    } catch (UserNotFoundException $e) {}

    try {
      if (User::findIdByUsername($username)) {
        $model->error = 'USERNAME_TAKEN';
        return;
      }
    } catch (UserNotFoundException $e) {}

    try {

      $user = new User(null);
      $user->setEmail($email);
      $user->setPassword($pw1);
      $user->setUsername($username);
      $user->setVerified(false, true);
      $user->commit();
      $user_id = $user->getId();
      $model->error = false;

    } catch (QueryException $e) {

      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);
      $model->error = 'INTERNAL_ERROR';
      $user = null;
      $user_id = null;

    }

    if ($user) {
      Logger::logEvent(
        EventTypes::USER_CREATED,
        $user_id,
        getenv('REMOTE_ADDR'),
        json_encode([
          'error'           => $model->error,
          'error_extra'     => $model->error_extra,
          'requirements'    => $req,
          'email'           => $email,
          'username'        => $username,
          'display_name'    => null,
          'options_bitmask' => 0,
        ])
      );

      $state = new StdClass();

      $mail = new PHPMailer( true ); // true enables exceptions
      $mail_config = Common::$config->email;

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

        Logger::logEvent(
          EventTypes::EMAIL_SENT,
          $user_id,
          getenv('REMOTE_ADDR'),
          json_encode([
            'from' => $mail->From,
            'to' => $mail->getToAddresses(),
            'reply_to' => $mail->getReplyToAddresses(),
            'subject' => $mail->Subject,
            'content_type' => $mail->ContentType,
            'body' => $mail->Body,
            'alt_body' => $mail->AltBody,
          ])
        );

      } catch (\Exception $e) {
        $model->error = 'EMAIL_FAILURE';
      }
    }
  }
}
