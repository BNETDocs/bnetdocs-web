<?php

namespace BNETDocs\Models;

use \BNETDocs\Libraries\Model;

class Legal extends Model {

  public $user_session;

  public function __construct() {
    parent::__construct();
    $this->license      = null;
    $this->user_session = null;
  }

}
