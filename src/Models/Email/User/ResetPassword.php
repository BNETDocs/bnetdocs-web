<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Models\Email\User;
class ResetPassword extends Base
{
  public $email;
  public $token;
  public $username;
}
