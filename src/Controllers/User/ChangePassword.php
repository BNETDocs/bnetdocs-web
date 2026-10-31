<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Core\Config;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\User\User;
use \BNETDocs\Models\User\ChangePassword as ChangePasswordModel;

class ChangePassword extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new ChangePasswordModel();
  }

  public function invoke(?array $args): bool
  {
    if (!$this->model->active_user)
      $this->model->error = ChangePasswordModel::ERROR_NOT_LOGGED_IN;
    else if (Router::requestMethod() == Router::METHOD_POST)
      $this->tryChangePassword();

    $this->model->_responseCode = \BNETDocs\Libraries\Core\HttpCode::HTTP_OK;
    return true;
  }

  protected function tryChangePassword(): void
  {
    $q = Router::query();
    $pw1 = isset($q['pw1']) ? $q['pw1'] : null;
    $pw2 = isset($q['pw2']) ? $q['pw2'] : null;
    $pw3 = isset($q['pw3']) ? $q['pw3'] : null;

    if ($pw2 !== $pw3)
    {
      $this->model->error = ChangePasswordModel::ERROR_NONMATCHING_PASSWORD;
      return;
    }

    if (!$this->model->active_user->checkPassword($pw1))
    {
      $this->model->error = ChangePasswordModel::ERROR_PASSWORD_INCORRECT;
      return;
    }

    $pwlen = strlen($pw2);
    $req = Config::get('bnetdocs.user_register_requirements') ?? [];
    $email = $this->model->active_user->getEmail();
    $username = $this->model->active_user->getUsername();

    if (!($req['password_allow_email'] ?? false) && stripos($pw2, $email))
    {
      $this->model->error = ChangePasswordModel::ERROR_PASSWORD_CONTAINS_EMAIL;
      return;
    }

    if (!($req['password_allow_username'] ?? false) && stripos($pw2, $username))
    {
      $this->model->error = ChangePasswordModel::ERROR_PASSWORD_CONTAINS_USERNAME;
      return;
    }

    if (is_numeric($req['password_length_max'] ?? User::MAX_PASSWORD_HASH)
      && $pwlen > ($req['password_length_max'] ?? User::MAX_PASSWORD_HASH))
    {
      $this->model->error = ChangePasswordModel::ERROR_PASSWORD_TOO_LONG;
      return;
    }

    if (is_numeric($req['password_length_min'] ?? 4)
      && $pwlen < ($req['password_length_min'] ?? 4))
    {
      $this->model->error = ChangePasswordModel::ERROR_PASSWORD_TOO_SHORT;
      return;
    }

    $denylist = Config::get('bnetdocs.user_password_denylist_map') ?? '../etc/password_denylist.json';
    $denylist = json_decode(file_get_contents('./' . $denylist));
    foreach ($denylist as $denylist_pw)
    {
      if (strtolower($denylist_pw->password) == strtolower($pw2))
      {
        $this->model->error = ChangePasswordModel::ERROR_PASSWORD_DENYLIST;
        $this->model->denylist_reason = $denylist_pw->reason;
        return;
      }
    }

    $old_password_hash = $this->model->active_user->getPasswordHash();
    $old_password_salt = $this->model->active_user->getPasswordSalt();

    $this->model->active_user->setPassword($pw2);
    $this->model->error = $this->model->active_user->commit() ? false : ChangePasswordModel::ERROR_INTERNAL;

    $event = Logger::initEvent(
      \BNETDocs\Libraries\EventLog\EventTypes::USER_PASSWORD_CHANGE,
      $this->model->active_user,
      getenv('REMOTE_ADDR'),
      [
        'error' => $this->model->error,
        'old_password_hash' => $old_password_hash,
        'old_password_salt' => $old_password_salt,
        'new_password_hash' => $this->model->active_user->getPasswordHash(),
        'new_password_salt' => $this->model->active_user->getPasswordSalt()
      ]
    );

    if ($event->commit())
    {
      $embed = Logger::initDiscordEmbed($event, $this->model->active_user->getURI());
      Logger::logToDiscord($event, $embed);
    }
  }
}
