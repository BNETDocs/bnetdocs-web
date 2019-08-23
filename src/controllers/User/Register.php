<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\CSRF;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\Exceptions\RecaptchaException;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Recaptcha;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\Register as UserRegisterModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

use \PHPMailer\PHPMailer\Exception;
use \PHPMailer\PHPMailer\PHPMailer;

use \StdClass;

class Register extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $conf = &Common::$config; // local variable for accessing config.

    $model               = new UserRegisterModel();
    $model->csrf_id      = mt_rand();
    $model->csrf_token   = CSRF::generate($model->csrf_id);
    $model->error        = null;
    $model->recaptcha    = new Recaptcha(
      $conf->recaptcha->secret,
      $conf->recaptcha->sitekey,
      $conf->recaptcha->url
    );

    $model->username_max_len =
      $conf->bnetdocs->user_register_requirements->username_length_max;

    if ($router->getRequestMethod() == "POST") {
      $this->tryRegister($router, $model);
    }

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

  protected function tryRegister(Router &$router, UserRegisterModel &$model) {
    $data = $router->getRequestBodyArray();
    $model->email    = (isset($data["email"   ]) ? $data["email"   ] : null);
    $model->username = (isset($data["username"]) ? $data["username"] : null);
    if ( isset( Authentication::$user )) {
      $model->error = "ALREADY_LOGGED_IN";
      return;
    }
    $csrf_id    = (isset($data["csrf_id"   ]) ? $data["csrf_id"   ] : null);
    $csrf_token = (isset($data["csrf_token"]) ? $data["csrf_token"] : null);
    $csrf_valid = CSRF::validate($csrf_id, $csrf_token);
    if (!$csrf_valid) {
      $model->error = "INVALID_CSRF";
      return;
    }
    CSRF::invalidate($csrf_id);
    $email    = $model->email;
    $username = $model->username;
    $pw1      = (isset($data["pw1"]) ? $data["pw1"] : null);
    $pw2      = (isset($data["pw2"]) ? $data["pw2"] : null);
    $captcha  = (
      isset($data["g-recaptcha-response"]) ?
      $data["g-recaptcha-response"]        :
      null
    );
    try {
      if (!$model->recaptcha->verify($captcha, getenv("REMOTE_ADDR"))) {
        $model->error = "INVALID_CAPTCHA";
        return;
      }
    } catch (RecaptchaException $e) {
      $model->error = "INVALID_CAPTCHA";
      return;
    }
    if ($pw1 !== $pw2) {
      $model->error = "NONMATCHING_PASSWORD";
      return;
    }
    $pwlen       = strlen($pw1);
    $usernamelen = strlen($username);
    $req = &Common::$config->bnetdocs->user_register_requirements;
    if ($req->email_validate_quick
      && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $model->error = "INVALID_EMAIL";
      return;
    }
    if (!$req->password_allow_email && stripos($pw1, $email)) {
      $model->error = "PASSWORD_CONTAINS_EMAIL";
      return;
    }
    if (!$req->password_allow_username && stripos($pw1, $username)) {
      $model->error = "PASSWORD_CONTAINS_USERNAME";
      return;
    }
    if (is_numeric($req->username_length_max)
      && $usernamelen > $req->username_length_max) {
      $model->error = "USERNAME_TOO_LONG";
      return;
    }
    if (is_numeric($req->username_length_min)
      && $usernamelen < $req->username_length_min) {
      $model->error = "USERNAME_TOO_SHORT";
      return;
    }
    if (is_numeric($req->password_length_max)
      && $pwlen > $req->password_length_max) {
      $model->error = "PASSWORD_TOO_LONG";
      return;
    }
    if (is_numeric($req->password_length_min)
      && $pwlen < $req->password_length_min) {
      $model->error = "PASSWORD_TOO_SHORT";
      return;
    }
    if (Common::$config->bnetdocs->user_register_disabled) {
      $model->error = "REGISTER_DISABLED";
      return;
    }

    try {
      if (!$req->email_duplicate_allowed && User::findIdByEmail($email)) {
        $model->error = "EMAIL_ALREADY_USED";
        return;
      }
    } catch (UserNotFoundException $e) {}

    try {
      if (User::findIdByUsername($username)) {
        $model->error = "USERNAME_TAKEN";
        return;
      }
    } catch (UserNotFoundException $e) {}

    $user = null;
    $user_id = null;

    try {

      $success = User::create(
        $email, $username, null, $pw1, User::DEFAULT_OPTION
      );

      if ($success) {
        $user_id = User::findIdByUsername($username);
        $user = new User( $user_id );
      }

    } catch (QueryException $e) {

      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);

    }

    if ($success) {
      $state = new StdClass();

      $mail = new PHPMailer( true ); // true enables exceptions
      $mail_config = Common::$config->email;

      $state->mail &= $mail;
      $state->token = ( $user ? $user->getVerificationToken() : null );

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
        if (!empty($mail_config->recipient_from)) {
          $mail->setFrom($mail_config->recipient_from, 'BNETDocs');
        }

        $mail->addAddress($email);

        if (!empty($mail_config->recipient_reply_to)) {
          $mail->addReplyTo($mail_config->recipient_reply_to);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Account Activation';
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        ob_start();
        (new Template($mail, 'Email/User/Register.rich'))->render();
        $mail->Body = ob_get_clean();

        ob_start();
        (new Template($mail, 'Email/User/Register.plain'))->render();
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
        $model->error = "EMAIL_FAILURE";
      }
    }

    if (!$success) {
      $model->error = "INTERNAL_ERROR";
    } else {
      $model->error = false;
    }
    Logger::logEvent(
      EventTypes::USER_CREATED,
      $user_id,
      getenv("REMOTE_ADDR"),
      json_encode([
        "error"           => $model->error,
        "requirements"    => $req,
        "email"           => $email,
        "username"        => $username,
        "display_name"    => null,
        "options_bitmask" => 0,
      ])
    );
  }

}
