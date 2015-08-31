<?php

namespace BNETDocs\Models\User;

use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Model;

class Login extends Model {

  public $bad_email;
  public $bad_password;
  public $email;
  public $password;

  public function __construct() {
    parent::__construct();
    $this->bad_email    = null;
    $this->bad_password = null;
    $this->email        = null;
    $this->password     = null;
  }

}
