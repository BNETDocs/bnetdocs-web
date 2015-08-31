<?php

namespace BNETDocs\Models\User;

use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Model;

class Login extends Model {

  public $email;
  public $login_result;
  public $password;

  public function __construct() {
    parent::__construct();
    $this->email        = null;
    $this->login_result = null;
    $this->password     = null;
  }

}
