<?php

namespace BNETDocs\Models\User;

use \CarlBennett\MVC\Libraries\Model;

class ResetPassword extends Model {

  public $csrf_id;
  public $csrf_token;
  public $error;
  public $token;
  public $user;
  public $username;

}
