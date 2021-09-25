<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Models\User;
class ResetPassword extends \BNETDocs\Models\ActiveUser
{
  const E_BAD_EMAIL = 'BAD_EMAIL';
  const E_BAD_TOKEN = 'BAD_TOKEN';
  const E_EMPTY_EMAIL = 'EMPTY_EMAIL';
  const E_SUCCESS = 'SUCCESS';
  const E_INTERNAL_ERROR = 'INTERNAL_ERROR';
  const E_PASSWORD_CONTAINS_EMAIL = 'PASSWORD_CONTAINS_EMAIL';
  const E_PASSWORD_CONTAINS_USERNAME = 'PASSWORD_CONTAINS_USERNAME';
  const E_PASSWORD_MISMATCH = 'PASSWORD_MISMATCH';
  const E_PASSWORD_TOO_LONG = 'PASSWORD_TOO_LONG';
  const E_PASSWORD_TOO_SHORT = 'PASSWORD_TOO_SHORT';
  const E_USER_DISABLED = 'USER_DISABLED';
  const E_USER_NOT_FOUND = 'USER_NOT_FOUND';

  public $email;
  public $error;
  public $form_fields;
  public $pw1;
  public $pw2;
  public $token;
  public $user;
}
