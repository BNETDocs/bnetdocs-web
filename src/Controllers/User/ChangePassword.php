<?php

namespace BNETDocs\Controllers\User;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Router;
use \Exception;

class ChangePassword extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\User\ChangePassword();
  }

  public function invoke(?array $args): bool
  {
    if (!$this->model->active_user)
      $this->model->error = 'NOT_LOGGED_IN';
    else if (Router::requestMethod() == Router::METHOD_POST)
      $this->tryChangePassword();

    $this->model->_responseCode = 200;
    return true;
  }

  protected function tryChangePassword() : void
  {
    $q = Router::query();
    $pw1 = isset($q['pw1']) ? $q['pw1'] : null;
    $pw2 = isset($q['pw2']) ? $q['pw2'] : null;
    $pw3 = isset($q['pw3']) ? $q['pw3'] : null;

    if ($pw2 !== $pw3)
    {
      $this->model->error = 'NONMATCHING_PASSWORD';
      return;
    }

    if (!$this->model->active_user->checkPassword($pw1))
    {
      $this->model->error = 'PASSWORD_INCORRECT';
      return;
    }

    $pwlen = strlen($pw2);
    $req = &Common::$config->bnetdocs->user_register_requirements;
    $email = $this->model->active_user->getEmail();
    $username = $this->model->active_user->getUsername();

    if (!$req->password_allow_email && stripos($pw2, $email))
    {
      $this->model->error = 'PASSWORD_CONTAINS_EMAIL';
      return;
    }

    if (!$req->password_allow_username && stripos($pw2, $username))
    {
      $this->model->error = 'PASSWORD_CONTAINS_USERNAME';
      return;
    }

    if (is_numeric($req->password_length_max) && $pwlen > $req->password_length_max)
    {
      $this->model->error = 'PASSWORD_TOO_LONG';
      return;
    }

    if (is_numeric($req->password_length_min) && $pwlen < $req->password_length_min)
    {
      $this->model->error = 'PASSWORD_TOO_SHORT';
      return;
    }

    $denylist = Common::$config->bnetdocs->user_password_denylist_map;
    $denylist = json_decode(file_get_contents('./' . $denylist));
    foreach ($denylist as $denylist_pw)
    {
      if (strtolower($denylist_pw->password) == strtolower($pw2))
      {
        $this->model->error = 'PASSWORD_BLACKLIST';
        $this->model->error_extra = $denylist_pw->reason;
        return;
      }
    }

    $old_password_hash = $this->model->active_user->getPasswordHash();
    $old_password_salt = $this->model->active_user->getPasswordSalt();

    $this->model->active_user->setPassword($pw2);
    $this->model->error = $this->model->active_user->commit() ? false : 'INTERNAL_ERROR';

    \BNETDocs\Libraries\Event::log(
      \BNETDocs\Libraries\EventTypes::USER_PASSWORD_CHANGE,
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
  }
}
