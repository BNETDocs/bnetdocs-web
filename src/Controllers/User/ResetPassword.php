<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Core\Config;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\EventLog\EventTypes;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\User\ResetPasswordEmail;
use \BNETDocs\Libraries\User\User;
use \BNETDocs\Models\User\ResetPassword as ResetPasswordModel;
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
    try
    {
      ResetPasswordEmail::send($this->model->user, $this->model->user);
    }
    catch (\PHPMailer\PHPMailer\Exception)
    {
      return ResetPasswordModel::ERROR_INTERNAL;
    }

    return false;
  }
}
