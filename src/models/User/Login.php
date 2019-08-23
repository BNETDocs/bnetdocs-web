<?php

namespace BNETDocs\Models\User;

use \CarlBennett\MVC\Libraries\Model;

class Login extends Model {

  public $csrf_id;
  public $csrf_token;
  public $error;
  public $username;

}
