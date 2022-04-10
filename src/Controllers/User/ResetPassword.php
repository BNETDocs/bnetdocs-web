<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Template;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\ResetPassword as ResetPasswordModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \Exception;
use \InvalidArgumentException;
use \PHPMailer\PHPMailer\PHPMailer;
use \StdClass;
use \UnexpectedValueException;

class ResetPassword extends Controller
{
  public function &run(Router &$router, View &$view, array &$args)
  {
    $model = new ResetPasswordModel();
    $model->active_user = Authentication::$user;
    $model->form_fields = array_merge(
      // Conflicting request query string fields will be overridden by POST-body fields
      $router->getRequestQueryArray() ?? [], $router->getRequestBodyArray() ?? []
    );

    $model->email = $model->form_fields['email'] ?? null;
    $model->pw1 = $model->form_fields['pw1'] ?? null;
    $model->pw2 = $model->form_fields['pw2'] ?? null;
    $model->token = $model->form_fields['t'] ?? null;

    if ($router->getRequestMethod() == 'POST')
    {
      $model->error = $this->doPasswordReset($model);
      Logger::logEvent(
        EventTypes::USER_PASSWORD_RESET,
        ($model->user ? $model->user->getId() : null),
        getenv('REMOTE_ADDR'),
        json_encode([
          'active_user' => $model->active_user,
          'email' => $model->email,
          'error' => $model->error,
          'user' => $model->user,
        ])
      );
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }

  protected function doPasswordReset(ResetPasswordModel &$model)
  {
    if (empty($model->email))
    {
      return ResetPasswordModel::E_EMPTY_EMAIL;
    }

    try
    {
      $user_id = User::findIdByEmail($model->email);
      if ($user_id !== false) $model->user = new User($user_id);
    }
    catch (UserNotFoundException $e)
    {
      $model->user = null;
    }
    catch (UnexpectedValueException $e)
    {
      return ResetPasswordModel::E_BAD_EMAIL;
    }

    if (!$model->user)
      return ResetPasswordModel::E_USER_NOT_FOUND;

    if (empty($model->token))
    {
      // User sent POST with email but no token, send email with new token to verify
      try
      {
        $model->user->setVerifierToken(
          User::generateVerifierToken($model->user->getUsername() ?? '', $model->user->getEmail() ?? '')
        );
        $model->user->commit();
      }
      catch (Exception $e)
      {
        return ResetPasswordModel::E_INTERNAL_ERROR;
      }
      return self::sendEmail($model);
    }

    if ($model->token !== $model->user->getVerifierToken())
    {
      return ResetPasswordModel::E_BAD_TOKEN;
    }

    if ($model->pw1 !== $model->pw2)
    {
      return ResetPasswordModel::E_PASSWORD_MISMATCH;
    }

    $req = Common::$config->bnetdocs->user_register_requirements;
    $pwlen = strlen($model->pw1);

    if (is_numeric($req->password_length_max) && $pwlen > $req->password_length_max)
    {
      return ResetPasswordModel::E_PASSWORD_TOO_LONG;
    }

    if (is_numeric($req->password_length_min) && $pwlen < $req->password_length_min)
    {
      return ResetPasswordModel::E_PASSWORD_TOO_SHORT;
    }

    if (!$req->password_allow_email && stripos($model->pw1, $model->user->getEmail()))
    {
      return ResetPasswordModel::E_PASSWORD_CONTAINS_EMAIL;
    }

    if (!$req->password_allow_username && stripos($model->pw1, $model->user->getUsername()))
    {
      return ResetPasswordModel::E_PASSWORD_CONTAINS_USERNAME;
    }

    if ($model->user->isDisabled())
    {
      return ResetPasswordModel::E_USER_DISABLED;
    }

    try
    {
      $model->user->setPassword($model->pw1);
      $model->user->setVerified(true);
      $model->user->commit();
    }
    catch (Exception $e)
    {
      return ResetPasswordModel::E_INTERNAL_ERROR;
    }

    return ResetPasswordModel::E_SUCCESS;
  }

  protected function sendEmail(ResetPasswordModel &$model)
  {
    $mail = new PHPMailer(true); // true enables exceptions
    $mail_config = Common::$config->email;

    $state = new StdClass();
    $state->email = $model->user->getEmail();
    $state->mail = &$mail;
    $state->token = $model->user->getVerifierToken();
    $state->username = $model->user->getName();

    try
    {
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
      if (isset($mail_config->recipient_from[0]))
      {
        $mail->setFrom($mail_config->recipient_from[0], $mail_config->recipient_from[1]);
      }

      $mail->addAddress($model->user->getEmail(), $model->user->getName());

      if (isset($mail_config->recipient_reply_to[0]))
      {
        $mail->addReplyTo($mail_config->recipient_reply_to[0], $mail_config->recipient_reply_to[1]);
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

      Logger::logEvent(
        EventTypes::EMAIL_SENT,
        $model->user->getId(),
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
    }
    catch (Exception $e)
    {
      return ResetPasswordModel::E_INTERNAL_ERROR;
    }

    return ResetPasswordModel::E_SUCCESS;
  }
}
