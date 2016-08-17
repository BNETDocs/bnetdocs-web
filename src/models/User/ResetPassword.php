<?php

namespace BNETDocs\Models\User;

use \BNETDocs\Libraries\Model;

class ResetPassword extends Model {

  public $csrf_id;
  public $csrf_token;
  public $email;
  public $error;
  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->csrf_id      = null;
    $this->csrf_token   = null;
    $this->email        = null;
    $this->error        = null;
    $this->user_session = null;
  }

}
