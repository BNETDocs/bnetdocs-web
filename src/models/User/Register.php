<?php

namespace BNETDocs\Models\User;

use \CarlBennett\MVC\Libraries\Model;

class Register extends Model {

  public $email;
  public $recaptcha;
  public $username;
  public $username_max_len;

}
