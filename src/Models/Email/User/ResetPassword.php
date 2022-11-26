<?php

namespace BNETDocs\Models\Email\User;

class ResetPassword extends \BNETDocs\Models\Email\Base
{
  public $email;
  public $token;
  public $username;
}
