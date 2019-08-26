<?php

namespace BNETDocs\Controllers\User;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

use \BNETDocs\Libraries\CSRF;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;

use \BNETDocs\Models\User\ResetPassword as UserResetPasswordModel;

use \PHPMailer\PHPMailer\Exception;
use \PHPMailer\PHPMailer\PHPMailer;

use \InvalidArgumentException;
use \StdClass;

class ResetPassword extends Controller {
  const RET_FAILURE = 0;
  const RET_SUCCESS = 1;
  const RET_EMAIL   = 2;

  public function &run( Router &$router, View &$view, array &$args ) {

    if ( $router->getRequestMethod() == 'GET' ) {
      $data = $router->getRequestQueryArray();
    } else {
      $data = $router->getRequestBodyArray();
    }

    $model = new UserResetPasswordModel();

    $model->error = null;
    $model->csrf_id = mt_rand();
    $model->csrf_token = CSRF::generate( $model->csrf_id );
    $model->email = isset( $data[ 'email' ]) ? $data[ 'email' ] : null;
    $model->pw1 = isset( $data[ 'pw1' ]) ? $data[ 'pw1' ] : null;
    $model->pw2 = isset( $data[ 'pw2' ]) ? $data[ 'pw2' ] : null;
    $model->token = isset( $data[ 't' ]) ? $data[ 't' ] : null;
    $model->user = null;

    if ( $router->getRequestMethod() == 'POST' ) {
      $ret = $this->doPasswordReset( $model, $data );
      if ( $ret !== self::RET_EMAIL ) {
        Logger::logEvent(
          EventTypes::USER_PASSWORD_RESET,
          ( $model->user ? $model->user->getId() : null ),
          getenv( 'REMOTE_ADDR' ),
          json_encode([
            'error' => $model->error,
            'email' => $model->email,
            'user' => ( $model->user ? true : false ),
          ])
        );
      }
    }

    $view->render( $model );

    $model->_responseCode = 200;
    $model->_responseHeaders[ 'Content-Type' ] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

  protected function doPasswordReset( UserResetPasswordModel &$model, &$data ) {
    $model->error = 'INTERNAL_ERROR';

    $csrf_id = isset( $data[ 'csrf_id' ]) ? $data[ 'csrf_id' ] : null;
    $csrf_token = (
      isset( $data[ 'csrf_token' ]) ? $data[ 'csrf_token' ] : null
    );
    $csrf_valid = CSRF::validate( $csrf_id, $csrf_token );

    if ( !$csrf_valid ) {
      $model->error = 'INVALID_CSRF';
      return self::RET_FAILURE;
    }
    CSRF::invalidate( $csrf_id );

    if ( empty( $model->email )) {
      $model->error = 'EMPTY_EMAIL';
      return self::RET_FAILURE;
    }

    try {
      $model->user = new User( User::findIdByEmail( $model->email ));
    } catch ( UserNotFoundException $e ) {
      $model->user = null;
    } catch ( InvalidArgumentException $e ) {
      $model->user = null;
    }

    if ( !$model->user ) {
      $model->error = 'USER_NOT_FOUND';
      return self::RET_FAILURE;
    }

    if ( empty( $model->token )) {
      $state = new StdClass();

      $mail = new PHPMailer( true ); // true enables exceptions
      $mail_config = Common::$config->email;

      $state->mail = &$mail;
      $state->token = $model->user->getVerificationToken();
      $state->user = $model->user;

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

        $mail->addAddress($model->user->getEmail(), $model->user->getName());

        if (isset($mail_config->recipient_reply_to[0])) {
          $mail->addReplyTo(
            $mail_config->recipient_reply_to[0],
            $mail_config->recipient_reply_to[1]
          );
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Password';
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        ob_start();
        (new Template($state, 'Email/User/ResetPassword.rich'))->render();
        $mail->Body = ob_get_clean();

        ob_start();
        (new Template($state, 'Email/User/ResetPassword.plain'))->render();
        $mail->AltBody = ob_get_clean();

        $mail->send();

        $model->error = false;

        Logger::logEvent(
          EventTypes::EMAIL_SENT,
          $model->user->getId(),
          getenv( 'REMOTE_ADDR' ),
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

      return self::RET_EMAIL;
    }

    if ( $model->token !== $model->user->getVerificationToken() ) {
      $model->error = 'INVALID_TOKEN';
      return self::RET_FAILURE;
    }

    if ( $model->pw1 !== $model->pw2 ) {
      $model->error = 'PASSWORD_MISMATCH';
      return self::RET_FAILURE;
    }

    $req = Common::$config->bnetdocs->user_register_requirements;
    $pwlen = strlen( $model->pw1 );

    if ( is_numeric( $req->password_length_max )
      && $pwlen > $req->password_length_max ) {
      $model->error = 'PASSWORD_TOO_LONG';
      return self::RET_FAILURE;
    }

    if ( is_numeric( $req->password_length_min )
      && $pwlen < $req->password_length_min ) {
      $model->error = 'PASSWORD_TOO_SHORT';
      return self::RET_FAILURE;
    }

    if ( !$req->password_allow_email
      && stripos( $model->pw1, $model->user->getEmail() )) {
      $model->error = 'PASSWORD_CONTAINS_EMAIL';
      return self::RET_FAILURE;
    }

    if ( !$req->password_allow_username
      && stripos( $model->pw1, $model->user->getUsername() )) {
      $model->error = 'PASSWORD_CONTAINS_USERNAME';
      return self::RET_FAILURE;
    }

    // --
    $model->user->invalidateVerificationToken();
    // --

    if ( $model->user->isDisabled() ) {
      $model->error = 'USER_DISABLED';
      return self::RET_FAILURE;
    }

    if (!$model->user->changePassword( $model->pw1 )) {
      $model->error = 'INTERNAL_ERROR';
      return self::RET_FAILURE;
    }

    $model->error = false;
    return self::RET_SUCCESS;
  }
}
