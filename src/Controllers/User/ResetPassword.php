<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Core\Config;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\Core\Template;
use \BNETDocs\Libraries\EventLog\EventTypes;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\User\User;
use \BNETDocs\Models\User\ResetPassword as ResetPasswordModel;
use \PHPMailer\PHPMailer\PHPMailer;
use \StdClass;
use \UnexpectedValueException;

class ResetPassword extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new ResetPasswordModel();
  }

  public function invoke(?array $args): bool
  {
    $this->model->form_fields = Router::query();
    $this->model->email = $this->model->form_fields['email'] ?? null;
    $this->model->pw1 = $this->model->form_fields['pw1'] ?? null;
    $this->model->pw2 = $this->model->form_fields['pw2'] ?? null;
    $this->model->token = $this->model->form_fields['t'] ?? null;

    if (Router::requestMethod() == Router::METHOD_POST)
    {
      $this->model->error = $this->doPasswordReset();

      $event = Logger::initEvent(
        EventTypes::USER_PASSWORD_RESET,
        $this->model->active_user,
        getenv('REMOTE_ADDR'),
        [
          'active_user' => $this->model->active_user,
          'email' => $this->model->email,
          'error' => $this->model->error,
          'user' => $this->model->user,
        ]
      );

      if ($event->commit())
      {
        $embed = Logger::initDiscordEmbed($event, $this->model->active_user ? $this->model->active_user->getURI() : null);
        Logger::logToDiscord($event, $embed);
      }
    }

    $this->model->_responseCode = \BNETDocs\Libraries\Core\HttpCode::HTTP_OK;
    return true;
  }

  protected function doPasswordReset(): mixed
  {
    if (empty($this->model->email)) return ResetPasswordModel::ERROR_EMPTY_EMAIL;

    try
    {
      $user_id = User::findIdByEmail($this->model->email);
      if (!is_null($user_id)) $this->model->user = new User($user_id);
    }
    catch (UnexpectedValueException)
    {
      return ResetPasswordModel::ERROR_BAD_EMAIL;
    }

    if (!$this->model->user) return ResetPasswordModel::ERROR_USER_NOT_FOUND;

    if (empty($this->model->token))
    {
      // User sent POST with email but no token, send email with new token to verify
      $this->model->user->setVerifierToken(
        User::generateVerifierToken(
          $this->model->user->getUsername() ?? '', $this->model->user->getEmail() ?? ''
        )
      );

      return $this->model->user->commit() ? self::sendEmail() : ResetPasswordModel::ERROR_INTERNAL;
    }

    if ($this->model->token !== $this->model->user->getVerifierToken())
      return ResetPasswordModel::ERROR_BAD_TOKEN;

    if ($this->model->pw1 !== $this->model->pw2)
      return ResetPasswordModel::ERROR_PASSWORD_MISMATCH;

    $req = Config::get('bnetdocs.user_register_requirements') ?? [];
    $pwlen = strlen($this->model->pw1);

    if (is_numeric($req['password_length_max']) && $pwlen > $req['password_length_max'])
      return ResetPasswordModel::ERROR_PASSWORD_TOO_LONG;

    if (is_numeric($req['password_length_min']) && $pwlen < $req['password_length_min'])
      return ResetPasswordModel::ERROR_PASSWORD_TOO_SHORT;

    if (!$req['password_allow_email'] && stripos($this->model->pw1, $this->model->user->getEmail()))
      return ResetPasswordModel::ERROR_PASSWORD_CONTAINS_EMAIL;

    if (!$req['password_allow_username'] && stripos($this->model->pw1, $this->model->user->getUsername()))
      return ResetPasswordModel::ERROR_PASSWORD_CONTAINS_USERNAME;

    if ($this->model->user->isDisabled()) return ResetPasswordModel::ERROR_USER_DISABLED;

    $this->model->user->setPassword($this->model->pw1);
    $this->model->user->setVerified(true);
    return $this->model->user->commit() ? false : ResetPasswordModel::ERROR_INTERNAL;
  }

  protected function sendEmail(): mixed
  {
    $mail = new PHPMailer(true); // true enables exceptions

    $state = new StdClass();
    $state->email = $this->model->user->getEmail();
    $state->mail = &$mail;
    $state->token = $this->model->user->getVerifierToken();
    $state->username = $this->model->user->getName();

    try
    {
      //Server settings
      $mail->Timeout = 10; // default is 300 per RFC2821 $ 4.5.3.2
      $mail->SMTPDebug = 0;
      $mail->isSMTP();
      $mail->Host       = Config::get('email.smtp_host') ?? 'localhost';
      $mail->Username   = Config::get('email.smtp_user') ?? '';
      $mail->Password   = Config::get('email.smtp_password') ?? '';
      $mail->SMTPAuth   = !empty($mail->Username);
      $mail->SMTPSecure = (Config::get('email.smtp_tls') ?? false) ? 'tls' : '';
      $mail->Port       = Config::get('email.smtp_port') ?? 25;

      //Recipients
      $recipient_from = Config::get('email.recipient_from') ?? [];
      if (isset($recipient_from[0]))
      {
        $mail->setFrom($recipient_from[0], $recipient_from[1] ?? '');
      }

      $mail->addAddress($this->model->user->getEmail(), $this->model->user->getName());

      $recipient_reply_to = Config::get('email.recipient_reply_to') ?? [];
      if (isset($recipient_reply_to[0]))
      {
        $mail->addReplyTo($recipient_reply_to[0], $recipient_reply_to[1] ?? '');
      }

      // Content
      $mail->isHTML(true);
      $mail->Subject = 'Reset Password';
      $mail->CharSet = PHPMailer::CHARSET_UTF8;

      ob_start();
      (new Template($state, 'Email/User/ResetPassword.rich'))->invoke();
      $mail->Body = ob_get_clean();

      ob_start();
      (new Template($state, 'Email/User/ResetPassword.plain'))->invoke();
      $mail->AltBody = ob_get_clean();

      $mail->send();

      $event = Logger::initEvent(
        EventTypes::EMAIL_SENT,
        $this->model->user,
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

      if ($event->commit())
      {
        $embed = Logger::initDiscordEmbed($event, $this->model->user->getURI());
        Logger::logToDiscord($event, $embed);
      }
    }
    catch (\PHPMailer\PHPMailer\Exception)
    {
      return ResetPasswordModel::ERROR_INTERNAL;
    }

    return false;
  }
}
