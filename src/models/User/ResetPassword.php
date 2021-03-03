<?php

namespace BNETDocs\Models\User;

use \CarlBennett\MVC\Libraries\Model;

class ResetPassword extends Model {

  public $email;
  public $error;
  public $token;
  public $user;

}
