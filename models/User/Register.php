<?php

namespace BNETDocs\Models\User;

use \BNETDocs\Libraries\Model;

class Register extends Model {

  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->user_session = null;
  }

}
