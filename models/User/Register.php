<?php

namespace BNETDocs\Models\User;

use \BNETDocs\Libraries\Model;

class Register extends Model {

  public $email;
  public $recaptcha;
  public $user_session;
  public $username;

  public function __construct() {
    parent::__construct();
    $this->email        = null;
    $this->recaptcha    = null;
    $this->user_session = null;
    $this->username     = null;
  }

}
