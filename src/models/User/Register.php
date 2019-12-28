<?php

namespace BNETDocs\Models\User;

use \CarlBennett\MVC\Libraries\Model;

class Register extends Model {

  public $csrf_id;
  public $csrf_token;
  public $email;
  public $error;
  public $error_extra;
  public $recaptcha;
  public $username;
  public $username_max_len;

}
