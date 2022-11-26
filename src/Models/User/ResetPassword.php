<?php

namespace BNETDocs\Models\User;

class ResetPassword extends \BNETDocs\Models\ActiveUser
{
  public const E_BAD_EMAIL = 'BAD_EMAIL';
  public const E_BAD_TOKEN = 'BAD_TOKEN';
  public const E_EMPTY_EMAIL = 'EMPTY_EMAIL';
  public const E_SUCCESS = 'SUCCESS';
  public const E_INTERNAL_ERROR = 'INTERNAL_ERROR';
  public const E_PASSWORD_CONTAINS_EMAIL = 'PASSWORD_CONTAINS_EMAIL';
  public const E_PASSWORD_CONTAINS_USERNAME = 'PASSWORD_CONTAINS_USERNAME';
  public const E_PASSWORD_MISMATCH = 'PASSWORD_MISMATCH';
  public const E_PASSWORD_TOO_LONG = 'PASSWORD_TOO_LONG';
  public const E_PASSWORD_TOO_SHORT = 'PASSWORD_TOO_SHORT';
  public const E_USER_DISABLED = 'USER_DISABLED';
  public const E_USER_NOT_FOUND = 'USER_NOT_FOUND';

  public $email;
  public $form_fields;
  public $pw1;
  public $pw2;
  public $token;
  public $user;
}
