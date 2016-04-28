<?php

namespace BNETDocs\Models\User;

use \BNETDocs\Libraries\Model;

class Register extends Model {

  public $email;
  public $username;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->email        = null;
    $this->username     = null;
    $this->user_session = null;
  }

}
