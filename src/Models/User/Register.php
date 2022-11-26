<?php

namespace BNETDocs\Models\User;

class Register extends \BNETDocs\Models\ActiveUser
{
  public $email;
  public $error_extra;
  public $recaptcha;
  public $username;
  public $username_max_len;
}
