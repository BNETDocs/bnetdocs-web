<?php

namespace BNETDocs\Models\User;

use \CarlBennett\MVC\Libraries\Model;

class ResetPassword extends Model {

  public $csrf_id;
  public $csrf_token;
  public $email;
  public $error;

}
