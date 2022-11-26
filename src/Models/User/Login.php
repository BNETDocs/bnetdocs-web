<?php

namespace BNETDocs\Models\User;

class Login extends \BNETDocs\Models\ActiveUser
{
  public const ERROR_ALREADY_LOGGED_IN = 'ALREADY_LOGGED_IN';
  public const ERROR_EMPTY_EMAIL = 'EMPTY_EMAIL';
  public const ERROR_EMPTY_PASSWORD = 'EMPTY_PASSWORD';
  public const ERROR_INCORRECT_PASSWORD = 'INCORRECT_PASSWORD';
  public const ERROR_INTERNAL = 'INTERNAL';
  public const ERROR_NONE = 'NONE';
  public const ERROR_SUCCESS = 'SUCCESS';
  public const ERROR_SYSTEM_DISABLED = 'SYSTEM_DISABLED';
  public const ERROR_USER_DISABLED = 'USER_DISABLED';
  public const ERROR_USER_NOT_FOUND = 'USER_NOT_FOUND';
  public const ERROR_USER_NOT_VERIFIED = 'USER_NOT_VERIFIED';

  public ?string $email = null;
  public mixed $error = self::ERROR_INTERNAL;
  public ?string $password = null;
  public ?\BNETDocs\Libraries\User $user = null;
}
